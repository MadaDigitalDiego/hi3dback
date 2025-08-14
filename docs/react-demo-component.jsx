// 🚀 Composant de Démonstration React - Recherche Globale Hi3D
// Copiez ce fichier dans votre projet React pour une implémentation rapide

import React, { useState, useEffect } from 'react';
import axios from 'axios';

// Configuration API
const API_BASE_URL = 'http://localhost:8000/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Hook personnalisé pour la recherche avec debounce
const useDebounce = (value, delay) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

// Service de recherche
const searchService = {
  globalSearch: async (query, options = {}) => {
    const params = new URLSearchParams({
      q: query,
      per_page: options.perPage || 10,
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

  getSuggestions: async (query, limit = 5) => {
    const response = await apiClient.get(`/search/suggestions?q=${query}&limit=${limit}`);
    return response.data;
  },

  getStats: async () => {
    const response = await apiClient.get('/search/stats');
    return response.data;
  }
};

// Composant principal de démonstration
const Hi3DSearchDemo = () => {
  // États
  const [query, setQuery] = useState('');
  const [results, setResults] = useState(null);
  const [suggestions, setSuggestions] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [selectedTypes, setSelectedTypes] = useState([]);
  const [filters, setFilters] = useState({});
  const [showSuggestions, setShowSuggestions] = useState(false);

  // Debounce pour les recherches
  const debouncedQuery = useDebounce(query, 300);
  const debouncedSuggestionQuery = useDebounce(query, 200);

  // Types de recherche disponibles
  const searchTypes = [
    { key: 'professional_profiles', label: 'Professionnels', icon: '👨‍💼' },
    { key: 'service_offers', label: 'Services', icon: '🛠️' },
    { key: 'achievements', label: 'Réalisations', icon: '🏆' }
  ];

  // Charger les statistiques au montage
  useEffect(() => {
    const loadStats = async () => {
      try {
        const statsData = await searchService.getStats();
        setStats(statsData.data);
      } catch (err) {
        console.error('Erreur lors du chargement des statistiques:', err);
      }
    };
    loadStats();
  }, []);

  // Recherche principale
  useEffect(() => {
    if (debouncedQuery && debouncedQuery.length >= 2) {
      performSearch();
    } else {
      setResults(null);
    }
  }, [debouncedQuery, selectedTypes, filters]);

  // Suggestions
  useEffect(() => {
    if (debouncedSuggestionQuery && debouncedSuggestionQuery.length >= 1) {
      loadSuggestions();
    } else {
      setSuggestions([]);
    }
  }, [debouncedSuggestionQuery]);

  const performSearch = async () => {
    setLoading(true);
    setError(null);
    try {
      const searchData = await searchService.globalSearch(debouncedQuery, {
        types: selectedTypes,
        filters: filters,
        perPage: 15
      });
      setResults(searchData.data);
    } catch (err) {
      setError('Erreur lors de la recherche');
      console.error('Erreur de recherche:', err);
    } finally {
      setLoading(false);
    }
  };

  const loadSuggestions = async () => {
    try {
      const suggestionsData = await searchService.getSuggestions(debouncedSuggestionQuery, 5);
      setSuggestions(suggestionsData.data.suggestions || []);
    } catch (err) {
      console.error('Erreur lors du chargement des suggestions:', err);
    }
  };

  const handleTypeToggle = (type) => {
    setSelectedTypes(prev => 
      prev.includes(type) 
        ? prev.filter(t => t !== type)
        : [...prev, type]
    );
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({
      ...prev,
      [key]: value || undefined
    }));
  };

  const handleSuggestionClick = (suggestion) => {
    setQuery(suggestion);
    setShowSuggestions(false);
  };

  const renderResultCard = (result) => {
    const cardStyle = {
      border: '1px solid #e1e5e9',
      borderRadius: '8px',
      padding: '16px',
      marginBottom: '16px',
      backgroundColor: 'white',
      boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
      transition: 'transform 0.2s ease, box-shadow 0.2s ease'
    };

    const titleStyle = {
      fontSize: '18px',
      fontWeight: 'bold',
      marginBottom: '8px',
      color: '#333'
    };

    const subtitleStyle = {
      fontSize: '14px',
      color: '#666',
      marginBottom: '8px'
    };

    const scoreStyle = {
      fontSize: '12px',
      color: '#007bff',
      fontWeight: 'bold'
    };

    return (
      <div key={`${result.type}-${result.id}`} style={cardStyle}>
        <div style={titleStyle}>
          {result.type === 'professional_profile' && '👨‍💼 '}
          {result.type === 'service_offer' && '🛠️ '}
          {result.type === 'achievement' && '🏆 '}
          {result.title || result.full_name}
        </div>
        <div style={subtitleStyle}>{result.subtitle}</div>
        {result.score && (
          <div style={scoreStyle}>
            Score de pertinence: {Math.round(result.score * 100)}%
          </div>
        )}
      </div>
    );
  };

  // Styles inline pour la démo
  const containerStyle = {
    maxWidth: '1200px',
    margin: '0 auto',
    padding: '20px',
    fontFamily: 'Arial, sans-serif'
  };

  const headerStyle = {
    textAlign: 'center',
    marginBottom: '30px'
  };

  const searchInputStyle = {
    width: '100%',
    maxWidth: '600px',
    padding: '15px 20px',
    fontSize: '16px',
    border: '2px solid #e1e5e9',
    borderRadius: '25px',
    outline: 'none',
    margin: '0 auto',
    display: 'block'
  };

  const suggestionsStyle = {
    position: 'absolute',
    top: '100%',
    left: '0',
    right: '0',
    backgroundColor: 'white',
    border: '1px solid #e1e5e9',
    borderRadius: '8px',
    boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
    zIndex: 1000,
    maxHeight: '200px',
    overflowY: 'auto'
  };

  const suggestionItemStyle = {
    padding: '12px 20px',
    cursor: 'pointer',
    borderBottom: '1px solid #f8f9fa'
  };

  const filtersStyle = {
    display: 'flex',
    flexWrap: 'wrap',
    gap: '10px',
    justifyContent: 'center',
    margin: '20px 0'
  };

  const typeButtonStyle = (isActive) => ({
    padding: '8px 16px',
    border: `1px solid ${isActive ? '#007bff' : '#e1e5e9'}`,
    borderRadius: '20px',
    backgroundColor: isActive ? '#007bff' : 'white',
    color: isActive ? 'white' : '#333',
    cursor: 'pointer',
    fontSize: '14px'
  });

  const statsStyle = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
    gap: '16px',
    margin: '20px 0',
    padding: '20px',
    backgroundColor: '#f8f9fa',
    borderRadius: '8px'
  };

  const statItemStyle = {
    textAlign: 'center',
    padding: '16px',
    backgroundColor: 'white',
    borderRadius: '8px',
    boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
  };

  return (
    <div style={containerStyle}>
      <div style={headerStyle}>
        <h1>🔍 Recherche Globale Hi3D - Démonstration</h1>
        <p>Recherchez parmi nos professionnels, services et réalisations</p>
      </div>

      {/* Statistiques */}
      {stats && (
        <div style={statsStyle}>
          <div style={statItemStyle}>
            <h3>👨‍💼 Professionnels</h3>
            <p style={{fontSize: '24px', fontWeight: 'bold', color: '#007bff'}}>
              {stats.total_professionals}
            </p>
          </div>
          <div style={statItemStyle}>
            <h3>🛠️ Services</h3>
            <p style={{fontSize: '24px', fontWeight: 'bold', color: '#28a745'}}>
              {stats.total_services}
            </p>
          </div>
          <div style={statItemStyle}>
            <h3>🏆 Réalisations</h3>
            <p style={{fontSize: '24px', fontWeight: 'bold', color: '#ffc107'}}>
              {stats.total_achievements}
            </p>
          </div>
        </div>
      )}

      {/* Barre de recherche */}
      <div style={{position: 'relative', maxWidth: '600px', margin: '0 auto'}}>
        <input
          type="text"
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setShowSuggestions(true);
          }}
          onFocus={() => setShowSuggestions(true)}
          onBlur={() => setTimeout(() => setShowSuggestions(false), 200)}
          placeholder="Rechercher des professionnels, services, réalisations..."
          style={searchInputStyle}
        />

        {/* Suggestions */}
        {showSuggestions && suggestions.length > 0 && (
          <div style={suggestionsStyle}>
            {suggestions.map((suggestion, index) => (
              <div
                key={index}
                style={suggestionItemStyle}
                onClick={() => handleSuggestionClick(suggestion)}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'white'}
              >
                {suggestion}
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Filtres de type */}
      <div style={filtersStyle}>
        {searchTypes.map(type => (
          <button
            key={type.key}
            onClick={() => handleTypeToggle(type.key)}
            style={typeButtonStyle(selectedTypes.includes(type.key))}
          >
            {type.icon} {type.label}
          </button>
        ))}
      </div>

      {/* Filtres avancés */}
      <div style={filtersStyle}>
        <input
          type="text"
          placeholder="Ville"
          value={filters.city || ''}
          onChange={(e) => handleFilterChange('city', e.target.value)}
          style={{padding: '8px 12px', border: '1px solid #e1e5e9', borderRadius: '4px'}}
        />
        <input
          type="number"
          placeholder="Prix max"
          value={filters.max_price || ''}
          onChange={(e) => handleFilterChange('max_price', e.target.value)}
          style={{padding: '8px 12px', border: '1px solid #e1e5e9', borderRadius: '4px'}}
        />
        <input
          type="number"
          placeholder="Rating min"
          value={filters.min_rating || ''}
          onChange={(e) => handleFilterChange('min_rating', e.target.value)}
          style={{padding: '8px 12px', border: '1px solid #e1e5e9', borderRadius: '4px'}}
          step="0.1"
          min="0"
          max="5"
        />
      </div>

      {/* État de chargement */}
      {loading && (
        <div style={{textAlign: 'center', padding: '40px'}}>
          <div>🔄 Recherche en cours...</div>
        </div>
      )}

      {/* Erreur */}
      {error && (
        <div style={{
          textAlign: 'center', 
          padding: '20px', 
          backgroundColor: '#f8d7da', 
          color: '#721c24',
          borderRadius: '8px',
          margin: '20px 0'
        }}>
          ❌ {error}
        </div>
      )}

      {/* Résultats */}
      {results && (
        <div style={{marginTop: '30px'}}>
          <h2>
            {results.total_count} résultat{results.total_count > 1 ? 's' : ''} 
            pour "{results.query}"
          </h2>
          
          {results.combined_results.data.length > 0 ? (
            <div>
              {results.combined_results.data.map(renderResultCard)}
            </div>
          ) : (
            <div style={{textAlign: 'center', padding: '40px', color: '#666'}}>
              Aucun résultat trouvé. Essayez de modifier vos critères de recherche.
            </div>
          )}
        </div>
      )}

      {/* Message d'accueil */}
      {!query && !loading && (
        <div style={{textAlign: 'center', padding: '60px', color: '#666'}}>
          <h2>🌟 Découvrez notre plateforme</h2>
          <p>Commencez à taper pour rechercher parmi nos professionnels, services et réalisations.</p>
          <p><strong>Exemples de recherche :</strong> "Laravel", "Développeur", "Design", "Marketing"</p>
        </div>
      )}
    </div>
  );
};

export default Hi3DSearchDemo;
