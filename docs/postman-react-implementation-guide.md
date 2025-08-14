# 🚀 Guide Complet : Tests Postman et Implémentation React

## 📋 Table des Matières

1. [Tests avec Postman](#tests-avec-postman)
2. [Collection Postman Complète](#collection-postman-complète)
3. [Implémentation React](#implémentation-react)
4. [Composants React Prêts](#composants-react-prêts)
5. [Hooks Personnalisés](#hooks-personnalisés)
6. [Exemples d'Utilisation](#exemples-dutilisation)

---

## 🧪 Tests avec Postman

### **1. Configuration de l'Environnement Postman**

Créez un environnement Postman avec les variables suivantes :

```json
{
  "name": "Hi3D Search API",
  "values": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api",
      "enabled": true
    },
    {
      "key": "auth_token",
      "value": "your-sanctum-token-here",
      "enabled": true
    }
  ]
}
```

### **2. Headers Globaux**

Configurez ces headers pour toutes les requêtes :

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{auth_token}}
```

### **3. Tests de Base**

#### **A. Recherche Globale**
```http
GET {{base_url}}/search?q=Laravel&per_page=10

Tests Postman :
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success', true);
});

pm.test("Response has data structure", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('query');
    pm.expect(jsonData.data).to.have.property('total_count');
    pm.expect(jsonData.data).to.have.property('results_by_type');
});
```

#### **B. Recherche avec Filtres**
```http
GET {{base_url}}/search/professionals?q=Expert&filters[city]=Paris&filters[min_rating]=4

Tests Postman :
pm.test("Filtered results are correct", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.count).to.be.at.least(0);
    
    if (jsonData.data.results.length > 0) {
        jsonData.data.results.forEach(function(result) {
            pm.expect(result.location).to.include("Paris");
            pm.expect(parseFloat(result.rating)).to.be.at.least(4);
        });
    }
});
```

#### **C. Suggestions en Temps Réel**
```http
GET {{base_url}}/search/suggestions?q=Lar&limit=5

Tests Postman :
pm.test("Suggestions are returned", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('suggestions');
    pm.expect(jsonData.data.suggestions).to.be.an('array');
    pm.expect(jsonData.data.suggestions.length).to.be.at.most(5);
});
```

### **4. Tests de Performance**

```javascript
// Pre-request Script
pm.globals.set("start_time", new Date().getTime());

// Tests
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});

pm.test("Performance tracking", function () {
    const startTime = pm.globals.get("start_time");
    const endTime = new Date().getTime();
    const responseTime = endTime - startTime;
    
    console.log(`Total request time: ${responseTime}ms`);
    pm.globals.set("last_response_time", responseTime);
});
```

---

## 📦 Collection Postman Complète

### **Structure de la Collection**

```
Hi3D Global Search API/
├── 🔍 Search Endpoints/
│   ├── Global Search
│   ├── Search Professionals
│   ├── Search Services
│   ├── Search Achievements
│   └── Search Suggestions
├── 📊 Stats & Metrics/
│   ├── Search Statistics
│   ├── Popular Searches
│   ├── Real-time Metrics
│   └── Detailed Metrics
├── 🎯 Filtered Searches/
│   ├── Professionals by City
│   ├── Services by Price Range
│   ├── Professionals by Rating
│   └── Services by Category
├── ⚡ Performance Tests/
│   ├── Bulk Search Test
│   ├── Concurrent Requests
│   └── Rate Limiting Test
└── 🛠️ Admin Endpoints/
    ├── Clear Cache
    └── Clear Metrics
```

### **Variables d'Environnement Avancées**

```json
{
  "search_query": "Laravel",
  "test_city": "Paris",
  "test_rating": "4.5",
  "test_price": "1000",
  "pagination_size": "10",
  "suggestion_limit": "5"
}
```

---

## ⚛️ Implémentation React

### **1. Configuration de Base**

#### **A. Installation des Dépendances**
```bash
npm install axios react-query @tanstack/react-query
# ou
yarn add axios react-query @tanstack/react-query
```

#### **B. Configuration API Client**
```javascript
// src/services/api.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Intercepteur pour l'authentification
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Intercepteur pour les erreurs
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### **2. Services de Recherche**

```javascript
// src/services/searchService.js
import { apiClient } from './api';

export const searchService = {
  // Recherche globale
  globalSearch: async (query, options = {}) => {
    const params = new URLSearchParams({
      q: query,
      per_page: options.perPage || 15,
      page: options.page || 1,
    });

    if (options.types?.length) {
      options.types.forEach(type => params.append('types[]', type));
    }

    Object.entries(options.filters || {}).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach(v => params.append(`filters[${key}][]`, v));
      } else {
        params.append(`filters[${key}]`, value);
      }
    });

    const response = await apiClient.get(`/search?${params}`);
    return response.data;
  },

  // Recherche par type
  searchProfessionals: async (query, filters = {}) => {
    const params = new URLSearchParams({ q: query });
    
    Object.entries(filters).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach(v => params.append(`filters[${key}][]`, v));
      } else {
        params.append(`filters[${key}]`, value);
      }
    });

    const response = await apiClient.get(`/search/professionals?${params}`);
    return response.data;
  },

  searchServices: async (query, filters = {}) => {
    const params = new URLSearchParams({ q: query });
    
    Object.entries(filters).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach(v => params.append(`filters[${key}][]`, v));
      } else {
        params.append(`filters[${key}]`, value);
      }
    });

    const response = await apiClient.get(`/search/services?${params}`);
    return response.data;
  },

  searchAchievements: async (query, filters = {}) => {
    const params = new URLSearchParams({ q: query });
    
    Object.entries(filters).forEach(([key, value]) => {
      params.append(`filters[${key}]`, value);
    });

    const response = await apiClient.get(`/search/achievements?${params}`);
    return response.data;
  },

  // Suggestions
  getSuggestions: async (query, limit = 5) => {
    const response = await apiClient.get(`/search/suggestions?q=${query}&limit=${limit}`);
    return response.data;
  },

  // Statistiques
  getStats: async () => {
    const response = await apiClient.get('/search/stats');
    return response.data;
  },

  getPopularSearches: async () => {
    const response = await apiClient.get('/search/popular');
    return response.data;
  },

  getMetrics: async () => {
    const response = await apiClient.get('/search/metrics');
    return response.data;
  },

  getRealtimeMetrics: async () => {
    const response = await apiClient.get('/search/metrics/realtime');
    return response.data;
  },
};
```

### **3. Hooks Personnalisés**

```javascript
// src/hooks/useSearch.js
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { searchService } from '../services/searchService';
import { useState, useCallback, useEffect } from 'react';
import { debounce } from 'lodash';

export const useGlobalSearch = (query, options = {}) => {
  return useQuery({
    queryKey: ['globalSearch', query, options],
    queryFn: () => searchService.globalSearch(query, options),
    enabled: query && query.length >= 2,
    staleTime: 5 * 60 * 1000, // 5 minutes
    cacheTime: 10 * 60 * 1000, // 10 minutes
  });
};

export const useSearchProfessionals = (query, filters = {}) => {
  return useQuery({
    queryKey: ['searchProfessionals', query, filters],
    queryFn: () => searchService.searchProfessionals(query, filters),
    enabled: query && query.length >= 2,
    staleTime: 5 * 60 * 1000,
  });
};

export const useSearchServices = (query, filters = {}) => {
  return useQuery({
    queryKey: ['searchServices', query, filters],
    queryFn: () => searchService.searchServices(query, filters),
    enabled: query && query.length >= 2,
    staleTime: 5 * 60 * 1000,
  });
};

export const useSearchSuggestions = (query, limit = 5) => {
  return useQuery({
    queryKey: ['searchSuggestions', query, limit],
    queryFn: () => searchService.getSuggestions(query, limit),
    enabled: query && query.length >= 1,
    staleTime: 2 * 60 * 1000, // 2 minutes pour les suggestions
  });
};

// Hook pour la recherche avec debounce
export const useDebouncedSearch = (initialQuery = '', delay = 300) => {
  const [query, setQuery] = useState(initialQuery);
  const [debouncedQuery, setDebouncedQuery] = useState(initialQuery);

  const debouncedSetQuery = useCallback(
    debounce((value) => {
      setDebouncedQuery(value);
    }, delay),
    [delay]
  );

  useEffect(() => {
    debouncedSetQuery(query);
  }, [query, debouncedSetQuery]);

  return {
    query,
    debouncedQuery,
    setQuery,
  };
};

// Hook pour les statistiques
export const useSearchStats = () => {
  return useQuery({
    queryKey: ['searchStats'],
    queryFn: searchService.getStats,
    staleTime: 10 * 60 * 1000, // 10 minutes
  });
};

export const usePopularSearches = () => {
  return useQuery({
    queryKey: ['popularSearches'],
    queryFn: searchService.getPopularSearches,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

export const useRealtimeMetrics = () => {
  return useQuery({
    queryKey: ['realtimeMetrics'],
    queryFn: searchService.getRealtimeMetrics,
    refetchInterval: 30 * 1000, // Actualiser toutes les 30 secondes
    staleTime: 0, // Toujours considérer comme périmé
  });
};
```

---

## 🎨 Composants React Prêts

### **1. Composant de Recherche Globale**

```jsx
// src/components/GlobalSearch.jsx
import React, { useState } from 'react';
import { useGlobalSearch, useDebouncedSearch } from '../hooks/useSearch';
import SearchInput from './SearchInput';
import SearchResults from './SearchResults';
import SearchFilters from './SearchFilters';
import LoadingSpinner from './LoadingSpinner';
import ErrorMessage from './ErrorMessage';

const GlobalSearch = () => {
  const { query, debouncedQuery, setQuery } = useDebouncedSearch('', 300);
  const [filters, setFilters] = useState({});
  const [selectedTypes, setSelectedTypes] = useState([]);

  const {
    data: searchResults,
    isLoading,
    error,
    refetch
  } = useGlobalSearch(debouncedQuery, {
    filters,
    types: selectedTypes,
    perPage: 15
  });

  const handleFilterChange = (newFilters) => {
    setFilters(prev => ({ ...prev, ...newFilters }));
  };

  const handleTypeToggle = (type) => {
    setSelectedTypes(prev => 
      prev.includes(type) 
        ? prev.filter(t => t !== type)
        : [...prev, type]
    );
  };

  return (
    <div className="global-search">
      <div className="search-header">
        <SearchInput
          value={query}
          onChange={setQuery}
          placeholder="Rechercher des professionnels, services, réalisations..."
          isLoading={isLoading}
        />
        
        <SearchFilters
          filters={filters}
          selectedTypes={selectedTypes}
          onFilterChange={handleFilterChange}
          onTypeToggle={handleTypeToggle}
        />
      </div>

      <div className="search-content">
        {error && (
          <ErrorMessage 
            message="Erreur lors de la recherche" 
            onRetry={refetch}
          />
        )}

        {isLoading && <LoadingSpinner />}

        {searchResults && (
          <SearchResults 
            results={searchResults.data}
            query={debouncedQuery}
          />
        )}
      </div>
    </div>
  );
};

export default GlobalSearch;
```

### **2. Composant de Saisie avec Suggestions**

```jsx
// src/components/SearchInput.jsx
import React, { useState, useRef, useEffect } from 'react';
import { useSearchSuggestions } from '../hooks/useSearch';

const SearchInput = ({ 
  value, 
  onChange, 
  placeholder = "Rechercher...",
  isLoading = false 
}) => {
  const [showSuggestions, setShowSuggestions] = useState(false);
  const inputRef = useRef(null);
  const suggestionsRef = useRef(null);

  const { data: suggestions } = useSearchSuggestions(value, 5);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        suggestionsRef.current && 
        !suggestionsRef.current.contains(event.target) &&
        !inputRef.current.contains(event.target)
      ) {
        setShowSuggestions(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleInputChange = (e) => {
    onChange(e.target.value);
    setShowSuggestions(true);
  };

  const handleSuggestionClick = (suggestion) => {
    onChange(suggestion);
    setShowSuggestions(false);
    inputRef.current.focus();
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Escape') {
      setShowSuggestions(false);
    }
  };

  return (
    <div className="search-input-container">
      <div className="search-input-wrapper">
        <input
          ref={inputRef}
          type="text"
          value={value}
          onChange={handleInputChange}
          onFocus={() => setShowSuggestions(true)}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          className="search-input"
        />
        
        {isLoading && (
          <div className="search-loading">
            <div className="spinner"></div>
          </div>
        )}
      </div>

      {showSuggestions && suggestions?.data?.suggestions?.length > 0 && (
        <div ref={suggestionsRef} className="suggestions-dropdown">
          {suggestions.data.suggestions.map((suggestion, index) => (
            <div
              key={index}
              className="suggestion-item"
              onClick={() => handleSuggestionClick(suggestion)}
            >
              <span className="suggestion-text">{suggestion}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default SearchInput;
```

### **3. Composant de Résultats**

```jsx
// src/components/SearchResults.jsx
import React from 'react';
import ProfessionalCard from './cards/ProfessionalCard';
import ServiceCard from './cards/ServiceCard';
import AchievementCard from './cards/AchievementCard';
import Pagination from './Pagination';

const SearchResults = ({ results, query }) => {
  if (!results) return null;

  const { total_count, results_by_type, combined_results, pagination } = results;

  const renderResultCard = (result) => {
    switch (result.type) {
      case 'professional_profile':
        return <ProfessionalCard key={`prof-${result.id}`} professional={result} />;
      case 'service_offer':
        return <ServiceCard key={`service-${result.id}`} service={result} />;
      case 'achievement':
        return <AchievementCard key={`achievement-${result.id}`} achievement={result} />;
      default:
        return null;
    }
  };

  return (
    <div className="search-results">
      <div className="results-header">
        <h2>
          {total_count} résultat{total_count > 1 ? 's' : ''} pour "{query}"
        </h2>
        
        <div className="results-summary">
          {results_by_type.professional_profiles?.length > 0 && (
            <span className="result-type-count">
              {results_by_type.professional_profiles.length} professionnel{results_by_type.professional_profiles.length > 1 ? 's' : ''}
            </span>
          )}
          {results_by_type.service_offers?.length > 0 && (
            <span className="result-type-count">
              {results_by_type.service_offers.length} service{results_by_type.service_offers.length > 1 ? 's' : ''}
            </span>
          )}
          {results_by_type.achievements?.length > 0 && (
            <span className="result-type-count">
              {results_by_type.achievements.length} réalisation{results_by_type.achievements.length > 1 ? 's' : ''}
            </span>
          )}
        </div>
      </div>

      <div className="results-grid">
        {combined_results.data.map(renderResultCard)}
      </div>

      {pagination && pagination.last_page > 1 && (
        <Pagination 
          currentPage={pagination.current_page}
          totalPages={pagination.last_page}
          onPageChange={(page) => {
            // Gérer le changement de page
          }}
        />
      )}
    </div>
  );
};

export default SearchResults;
```

### **4. Composants de Cartes**

```jsx
// src/components/cards/ProfessionalCard.jsx
import React from 'react';

const ProfessionalCard = ({ professional }) => {
  return (
    <div className="professional-card">
      <div className="card-header">
        <div className="avatar">
          {professional.avatar_url ? (
            <img src={professional.avatar_url} alt={professional.full_name} />
          ) : (
            <div className="avatar-placeholder">
              {professional.first_name?.[0]}{professional.last_name?.[0]}
            </div>
          )}
        </div>
        <div className="professional-info">
          <h3 className="name">{professional.full_name}</h3>
          <p className="title">{professional.title}</p>
          <p className="location">{professional.location}</p>
        </div>
        <div className="rating">
          <span className="rating-value">⭐ {professional.rating}</span>
        </div>
      </div>

      <div className="card-body">
        <p className="bio">{professional.bio}</p>

        <div className="skills">
          {professional.skills?.slice(0, 3).map((skill, index) => (
            <span key={index} className="skill-tag">{skill}</span>
          ))}
          {professional.skills?.length > 3 && (
            <span className="skill-more">+{professional.skills.length - 3}</span>
          )}
        </div>

        <div className="professional-stats">
          <div className="stat">
            <span className="stat-label">Expérience</span>
            <span className="stat-value">{professional.years_of_experience} ans</span>
          </div>
          <div className="stat">
            <span className="stat-label">Taux horaire</span>
            <span className="stat-value">{professional.hourly_rate}€/h</span>
          </div>
          <div className="stat">
            <span className="stat-label">Disponibilité</span>
            <span className={`stat-value status-${professional.availability_status}`}>
              {professional.availability_status === 'available' ? 'Disponible' : 'Occupé'}
            </span>
          </div>
        </div>
      </div>

      <div className="card-footer">
        <button className="btn-primary">Voir le profil</button>
        <button className="btn-secondary">Contacter</button>
      </div>
    </div>
  );
};

export default ProfessionalCard;
```

```jsx
// src/components/cards/ServiceCard.jsx
import React from 'react';

const ServiceCard = ({ service }) => {
  return (
    <div className="service-card">
      <div className="card-header">
        {service.image_url && (
          <img src={service.image_url} alt={service.title} className="service-image" />
        )}
        <div className="service-info">
          <h3 className="title">{service.title}</h3>
          <p className="provider">Par {service.provider_name}</p>
        </div>
        <div className="price">
          <span className="price-value">{service.price}€</span>
        </div>
      </div>

      <div className="card-body">
        <p className="description">{service.description}</p>

        <div className="service-details">
          <div className="detail">
            <span className="detail-label">Délai</span>
            <span className="detail-value">{service.execution_time}</span>
          </div>
          <div className="detail">
            <span className="detail-label">Révisions</span>
            <span className="detail-value">{service.revisions}</span>
          </div>
        </div>

        <div className="categories">
          {service.categories?.map((category, index) => (
            <span key={index} className="category-tag">{category}</span>
          ))}
        </div>

        <div className="service-stats">
          <span className="stat">⭐ {service.rating}</span>
          <span className="stat">👁️ {service.views} vues</span>
          <span className="stat">❤️ {service.likes} likes</span>
        </div>
      </div>

      <div className="card-footer">
        <button className="btn-primary">Commander</button>
        <button className="btn-secondary">Voir détails</button>
      </div>
    </div>
  );
};

export default ServiceCard;
```

### **5. Composants de Filtres**

```jsx
// src/components/SearchFilters.jsx
import React, { useState } from 'react';

const SearchFilters = ({
  filters,
  selectedTypes,
  onFilterChange,
  onTypeToggle
}) => {
  const [isExpanded, setIsExpanded] = useState(false);

  const searchTypes = [
    { key: 'professional_profiles', label: 'Professionnels', icon: '👨‍💼' },
    { key: 'service_offers', label: 'Services', icon: '🛠️' },
    { key: 'achievements', label: 'Réalisations', icon: '🏆' }
  ];

  const handlePriceRangeChange = (min, max) => {
    onFilterChange({
      min_price: min || undefined,
      max_price: max || undefined
    });
  };

  const handleRatingChange = (rating) => {
    onFilterChange({
      min_rating: rating || undefined
    });
  };

  return (
    <div className="search-filters">
      <div className="filter-types">
        {searchTypes.map(type => (
          <button
            key={type.key}
            className={`type-filter ${selectedTypes.includes(type.key) ? 'active' : ''}`}
            onClick={() => onTypeToggle(type.key)}
          >
            <span className="type-icon">{type.icon}</span>
            <span className="type-label">{type.label}</span>
          </button>
        ))}
      </div>

      <button
        className="filters-toggle"
        onClick={() => setIsExpanded(!isExpanded)}
      >
        Filtres avancés {isExpanded ? '▲' : '▼'}
      </button>

      {isExpanded && (
        <div className="advanced-filters">
          <div className="filter-group">
            <label>Localisation</label>
            <input
              type="text"
              placeholder="Ville"
              value={filters.city || ''}
              onChange={(e) => onFilterChange({ city: e.target.value || undefined })}
            />
          </div>

          <div className="filter-group">
            <label>Fourchette de prix</label>
            <div className="price-range">
              <input
                type="number"
                placeholder="Min"
                value={filters.min_price || ''}
                onChange={(e) => handlePriceRangeChange(e.target.value, filters.max_price)}
              />
              <span>-</span>
              <input
                type="number"
                placeholder="Max"
                value={filters.max_price || ''}
                onChange={(e) => handlePriceRangeChange(filters.min_price, e.target.value)}
              />
            </div>
          </div>

          <div className="filter-group">
            <label>Note minimum</label>
            <div className="rating-filter">
              {[1, 2, 3, 4, 5].map(rating => (
                <button
                  key={rating}
                  className={`rating-btn ${filters.min_rating >= rating ? 'active' : ''}`}
                  onClick={() => handleRatingChange(rating)}
                >
                  {'⭐'.repeat(rating)}
                </button>
              ))}
            </div>
          </div>

          <div className="filter-group">
            <label>Disponibilité</label>
            <select
              value={filters.availability_status || ''}
              onChange={(e) => onFilterChange({ availability_status: e.target.value || undefined })}
            >
              <option value="">Toutes</option>
              <option value="available">Disponible</option>
              <option value="busy">Occupé</option>
            </select>
          </div>

          <button
            className="clear-filters"
            onClick={() => onFilterChange({})}
          >
            Effacer les filtres
          </button>
        </div>
      )}
    </div>
  );
};

export default SearchFilters;
```

### **6. Hook de Recherche Avancée**

```jsx
// src/hooks/useAdvancedSearch.js
import { useState, useCallback, useMemo } from 'react';
import { useGlobalSearch } from './useSearch';

export const useAdvancedSearch = () => {
  const [searchState, setSearchState] = useState({
    query: '',
    filters: {},
    selectedTypes: [],
    sortBy: 'relevance',
    sortOrder: 'desc',
    page: 1,
    perPage: 15
  });

  const searchOptions = useMemo(() => ({
    filters: searchState.filters,
    types: searchState.selectedTypes,
    sortBy: searchState.sortBy,
    sortOrder: searchState.sortOrder,
    page: searchState.page,
    perPage: searchState.perPage
  }), [searchState]);

  const {
    data: searchResults,
    isLoading,
    error,
    refetch
  } = useGlobalSearch(searchState.query, searchOptions);

  const updateSearch = useCallback((updates) => {
    setSearchState(prev => ({
      ...prev,
      ...updates,
      page: updates.query !== prev.query ? 1 : (updates.page || prev.page)
    }));
  }, []);

  const setQuery = useCallback((query) => {
    updateSearch({ query, page: 1 });
  }, [updateSearch]);

  const setFilters = useCallback((filters) => {
    updateSearch({ filters, page: 1 });
  }, [updateSearch]);

  const toggleType = useCallback((type) => {
    setSearchState(prev => ({
      ...prev,
      selectedTypes: prev.selectedTypes.includes(type)
        ? prev.selectedTypes.filter(t => t !== type)
        : [...prev.selectedTypes, type],
      page: 1
    }));
  }, []);

  const setPage = useCallback((page) => {
    updateSearch({ page });
  }, [updateSearch]);

  const setSorting = useCallback((sortBy, sortOrder = 'desc') => {
    updateSearch({ sortBy, sortOrder, page: 1 });
  }, [updateSearch]);

  const clearFilters = useCallback(() => {
    updateSearch({
      filters: {},
      selectedTypes: [],
      page: 1
    });
  }, [updateSearch]);

  return {
    // État
    searchState,
    searchResults,
    isLoading,
    error,

    // Actions
    setQuery,
    setFilters,
    toggleType,
    setPage,
    setSorting,
    clearFilters,
    refetch,

    // Helpers
    hasFilters: Object.keys(searchState.filters).length > 0 || searchState.selectedTypes.length > 0,
    totalResults: searchResults?.data?.total_count || 0,
    hasResults: searchResults?.data?.total_count > 0
  };
};
```

---

## 🎯 Exemples d'Utilisation Complète

### **1. Page de Recherche Complète**

```jsx
// src/pages/SearchPage.jsx
import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAdvancedSearch } from '../hooks/useAdvancedSearch';
import SearchInput from '../components/SearchInput';
import SearchFilters from '../components/SearchFilters';
import SearchResults from '../components/SearchResults';
import SearchStats from '../components/SearchStats';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

const queryClient = new QueryClient();

const SearchPageContent = () => {
  const {
    searchState,
    searchResults,
    isLoading,
    error,
    setQuery,
    setFilters,
    toggleType,
    setPage,
    clearFilters,
    refetch,
    hasFilters,
    totalResults,
    hasResults
  } = useAdvancedSearch();

  return (
    <div className="search-page">
      <div className="search-container">
        <header className="search-header">
          <h1>Recherche Globale Hi3D</h1>
          <SearchInput
            value={searchState.query}
            onChange={setQuery}
            placeholder="Rechercher des professionnels, services, réalisations..."
            isLoading={isLoading}
          />
        </header>

        <div className="search-body">
          <aside className="search-sidebar">
            <SearchFilters
              filters={searchState.filters}
              selectedTypes={searchState.selectedTypes}
              onFilterChange={setFilters}
              onTypeToggle={toggleType}
            />

            {hasFilters && (
              <button
                className="clear-all-filters"
                onClick={clearFilters}
              >
                Effacer tous les filtres
              </button>
            )}

            <SearchStats />
          </aside>

          <main className="search-main">
            {error && (
              <ErrorMessage
                message="Erreur lors de la recherche"
                onRetry={refetch}
              />
            )}

            {isLoading && <LoadingSpinner />}

            {searchResults && hasResults && (
              <SearchResults
                results={searchResults.data}
                query={searchState.query}
                onPageChange={setPage}
              />
            )}

            {searchResults && !hasResults && searchState.query && (
              <div className="no-results">
                <h3>Aucun résultat trouvé</h3>
                <p>Essayez de modifier vos critères de recherche.</p>
              </div>
            )}

            {!searchState.query && (
              <div className="search-welcome">
                <h2>Découvrez notre plateforme</h2>
                <p>Recherchez parmi nos professionnels, services et réalisations.</p>
              </div>
            )}
          </main>
        </div>
      </div>
    </div>
  );
};

const SearchPage = () => (
  <QueryClientProvider client={queryClient}>
    <SearchPageContent />
  </QueryClientProvider>
);

export default SearchPage;
```

### **2. Composant de Recherche Rapide**

```jsx
// src/components/QuickSearch.jsx
import React, { useState } from 'react';
import { useDebouncedSearch, useGlobalSearch } from '../hooks/useSearch';

const QuickSearch = ({ onResultSelect, maxResults = 5 }) => {
  const [isOpen, setIsOpen] = useState(false);
  const { query, debouncedQuery, setQuery } = useDebouncedSearch('', 200);

  const { data: searchResults, isLoading } = useGlobalSearch(debouncedQuery, {
    perPage: maxResults
  });

  const handleResultClick = (result) => {
    onResultSelect(result);
    setIsOpen(false);
    setQuery('');
  };

  const results = searchResults?.data?.combined_results?.data || [];

  return (
    <div className="quick-search">
      <input
        type="text"
        value={query}
        onChange={(e) => {
          setQuery(e.target.value);
          setIsOpen(true);
        }}
        onFocus={() => setIsOpen(true)}
        onBlur={() => setTimeout(() => setIsOpen(false), 200)}
        placeholder="Recherche rapide..."
        className="quick-search-input"
      />

      {isOpen && (query.length >= 2) && (
        <div className="quick-search-dropdown">
          {isLoading && (
            <div className="quick-search-loading">Recherche...</div>
          )}

          {results.length > 0 && (
            <div className="quick-search-results">
              {results.map((result) => (
                <div
                  key={`${result.type}-${result.id}`}
                  className="quick-search-item"
                  onClick={() => handleResultClick(result)}
                >
                  <div className="result-type">
                    {result.type === 'professional_profile' && '👨‍💼'}
                    {result.type === 'service_offer' && '🛠️'}
                    {result.type === 'achievement' && '🏆'}
                  </div>
                  <div className="result-content">
                    <div className="result-title">{result.title || result.full_name}</div>
                    <div className="result-subtitle">{result.subtitle}</div>
                  </div>
                  <div className="result-score">
                    {result.score && `${Math.round(result.score * 100)}%`}
                  </div>
                </div>
              ))}
            </div>
          )}

          {!isLoading && results.length === 0 && query.length >= 2 && (
            <div className="quick-search-empty">
              Aucun résultat trouvé
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default QuickSearch;
```

### **3. Intégration avec React Router**

```jsx
// src/App.jsx
import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import SearchPage from './pages/SearchPage';
import ProfessionalDetail from './pages/ProfessionalDetail';
import ServiceDetail from './pages/ServiceDetail';
import Header from './components/Header';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      cacheTime: 10 * 60 * 1000, // 10 minutes
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <div className="App">
          <Header />
          <Routes>
            <Route path="/search" element={<SearchPage />} />
            <Route path="/professional/:id" element={<ProfessionalDetail />} />
            <Route path="/service/:id" element={<ServiceDetail />} />
            <Route path="/" element={<SearchPage />} />
          </Routes>
        </div>
      </Router>
    </QueryClientProvider>
  );
}

export default App;
```

---

## 🎨 Styles CSS Recommandés

```css
/* src/styles/search.css */
.search-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.search-header {
  text-align: center;
  margin-bottom: 30px;
}

.search-input-container {
  position: relative;
  max-width: 600px;
  margin: 0 auto;
}

.search-input {
  width: 100%;
  padding: 15px 20px;
  font-size: 16px;
  border: 2px solid #e1e5e9;
  border-radius: 25px;
  outline: none;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  border-color: #007bff;
}

.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  max-height: 300px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 12px 20px;
  cursor: pointer;
  border-bottom: 1px solid #f8f9fa;
  transition: background-color 0.2s ease;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

.search-body {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 30px;
}

.search-filters {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  height: fit-content;
}

.filter-types {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.type-filter {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e1e5e9;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.type-filter.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.professional-card,
.service-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-bottom: 20px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.professional-card:hover,
.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-bottom: 15px;
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.skills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 15px 0;
}

.skill-tag,
.category-tag {
  background: #e9ecef;
  color: #495057;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.btn-primary {
  background: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-secondary {
  background: transparent;
  color: #007bff;
  border: 1px solid #007bff;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #007bff;
  color: white;
}

@media (max-width: 768px) {
  .search-body {
    grid-template-columns: 1fr;
  }

  .search-filters {
    order: 2;
  }
}
```

---

## 🚀 Démarrage Rapide

### **1. Installation**
```bash
# Cloner et installer les dépendances
npm install axios @tanstack/react-query react-router-dom

# Ou avec yarn
yarn add axios @tanstack/react-query react-router-dom
```

### **2. Configuration**
```javascript
// .env
REACT_APP_API_URL=http://localhost:8000/api
```

### **3. Utilisation de Base**
```jsx
import React from 'react';
import GlobalSearch from './components/GlobalSearch';

function App() {
  return (
    <div className="App">
      <GlobalSearch />
    </div>
  );
}

export default App;
```

---

## 📝 Notes Importantes

### **Performance**
- Utilisez React Query pour le cache automatique
- Implémentez le debouncing pour les suggestions
- Optimisez les re-renders avec `useMemo` et `useCallback`

### **Accessibilité**
- Ajoutez les attributs ARIA appropriés
- Gérez la navigation au clavier
- Assurez-vous du contraste des couleurs

### **SEO**
- Utilisez des URLs avec paramètres de recherche
- Implémentez le server-side rendering si nécessaire
- Ajoutez les meta tags appropriés

Cette documentation complète vous permet d'implémenter rapidement et efficacement la recherche globale dans votre application React avec une expérience utilisateur optimale.
