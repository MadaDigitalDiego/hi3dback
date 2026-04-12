@php
$isAuthenticated = $isAuthenticated ?? false;
$authUser = $authUser ?? null;
$frontendUrl = $frontendUrl ?? rtrim(config('app.frontend_url', config('app.url')), '/');
$backendUrl = $backendUrl ?? rtrim(config('app.backend_url', config('app.url')), '/');
$apiBaseUrl = $apiBaseUrl ?? rtrim(config('app.api_base_url', $frontendUrl), '/');
$blogUrl = isset($blogUrl) ? rtrim($blogUrl, '/') : $frontendUrl;
$context = $context ?? 'default';
@endphp

{{-- Header principal - visible sur desktop --}}
<header class="sticky top-0 z-40 w-full py-5 bg-transparent" id="hi3dHeader">
  <div class="w-full flex flex-wrap items-center justify-between gap-3 min-h-[60px] px-[10px] md:px-[40px] lg:h-[60px]">
    {{-- Partie gauche - Logo --}}
    <div class="flex items-center gap-3 lg:gap-4 flex-shrink-0">
      <a href="{{ $frontendUrl ?? '/' }}" class="cursor-pointer m-0 p-0 leading-none">
        <img src="/img/logo.svg" alt="Hi3D Logo" class="m-0 p-0 block" style="width: 32px; height: 24px;" />
      </a>
    </div>

    {{-- Partie centrale - Barre de recherche (desktop only) --}}
    <div class="hidden lg:flex flex-1 justify-center px-4 xl:px-6">
      <div class="w-full max-w-[680px] xl:max-w-[700px] flex justify-center">
        <div class="relative" style="width: 506px;">
          <input
            type="text"
            id="hi3dSearchInput"
            autocomplete="off"
            autocorrect="off"
            autoCapitalize="none"
            spellCheck="false"
            inputMode="search"
            placeholder="Search"
            class="rounded-lg py-2 px-4 pr-48 focus:outline-none focus:ring-2 focus:ring-neutral-300 placeholder:text-[#666666]"
            style="width: 100%; height: 48px; font-family: 'Mona Sans', 'Inter', sans-serif; font-size: 16px; font-weight: 500; line-height: 24px; background-color: #F0F0F0; border-radius: 8px; color: #666666; border: none; letter-spacing: 0px; padding-right: 170px; padding-left: 52px;"
          />
          {{-- Type dropdown (Services / Artists) --}}
          <div class="absolute top-1/2 -translate-y-1/2 right-3">
            <div class="flex items-center" style="width: 160px; height: 28px; gap: 4px; border-radius: 4px; padding: 4px; background: #FFFFFF;">
              <button type="button" id="hi3dTypeServices" class="hi3d-type-btn flex items-center justify-center flex-1" style="height: 20px; border-radius: 4px; padding: 4px; background: #F0F0F0; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; line-height: 12px; color: #666666; border: none; cursor: pointer;">Services</button>
              <button type="button" id="hi3dTypeArtists" class="hi3d-type-btn flex items-center justify-center flex-1" style="height: 20px; border-radius: 4px; padding: 4px; background: #FFFFFF; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; line-height: 12px; color: #666666; border: none; cursor: pointer;">Artists</button>
            </div>
          </div>
          {{-- Search icon --}}
          <button type="button" id="hi3dSearchBtn" class="absolute left-3 top-1/2 -translate-y-1/2" style="pointer-events: auto;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#hi3dClipSearch)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.5304 11.16L18 15.6937L15.75 18L11.1656 13.527C10.0094 14.2465 8.67434 14.627 7.3125 14.625C3.27375 14.625 0 11.3434 0 7.3125C0 3.27375 3.28163 0 7.3125 0C11.3512 0 14.625 3.28163 14.625 7.3125C14.627 8.67212 14.2478 10.0051 13.5304 11.16ZM2.24662 7.2585C2.24662 10.0485 4.51238 12.321 7.30913 12.321C10.0991 12.321 12.3716 10.0541 12.3716 7.2585C12.3716 4.4685 10.1047 2.196 7.30913 2.196C4.51912 2.196 2.24662 4.46175 2.24662 7.2585Z" fill="black"/>
                <circle cx="6" cy="6" r="2" fill="#0D0D0D"/>
              </g>
              <defs>
                <clipPath id="hi3dClipSearch">
                  <rect width="18" height="18" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </button>
          {{-- Suggestions dropdown --}}
          <div id="hi3dSuggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-xl z-50 max-h-80 overflow-y-auto" style="border: 1px solid #E5E5E5;">
            <div id="hi3dSuggestionsList"></div>
          </div>
        </div>
      </div>
    </div>

    {{-- Partie droite - Boutons (desktop) --}}
    <div class="flex items-center gap-3 ml-auto">
      @if($isAuthenticated && $authUser)
        {{-- Utilisateur connecté - Barre d'icônes --}}
        <div class="text-black flex items-center gap-[10px]" style="width: 182px; height: 48px; border-radius: 8px; padding: 6px; background: #F0F0F0;">
          {{-- Bouton recherche (mobile only on desktop) --}}
          <button type="button" id="hi3dMobileSearchBtn" class="hidden lg:flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#hi3dClip0)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.46962 11.16L0 15.6937L2.25 18L6.83437 13.527C7.99062 14.2465 9.32566 14.627 10.6875 14.625C14.7262 14.625 18 11.3434 18 7.3125C18 3.27375 14.7184 0 10.6875 0C6.64875 0 3.375 3.28163 3.375 7.3125C3.37297 8.67212 3.75219 10.0051 4.46962 11.16ZM15.7534 7.2585C15.7534 10.0485 13.4876 12.321 10.6909 12.321C7.90088 12.321 5.62838 10.0541 5.62838 7.2585C5.62838 4.4685 7.89525 2.196 10.6909 2.196C13.4809 2.196 15.7534 4.46175 15.7534 7.2585Z" fill="black"/>
                <circle cx="2" cy="2" r="2" transform="matrix(-1 0 0 1 14 4)" fill="#0D0D0D"/>
              </g>
              <defs>
                <clipPath id="hi3dClip0">
                  <rect width="18" height="18" fill="white" transform="matrix(-1 0 0 1 18 0)"/>
                </clipPath>
              </defs>
            </svg>
          </button>
          {{-- Favoris --}}
          <a href="{{ $frontendUrl ?? '/' }}/favorite" class="flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12.9375 2.25H5.0625C4.76413 2.25 4.47798 2.36853 4.267 2.5795C4.05603 2.79048 3.9375 3.07663 3.9375 3.375V15.75C3.93755 15.8504 3.96446 15.9489 4.01545 16.0354C4.06643 16.1219 4.13963 16.1931 4.22744 16.2418C4.31525 16.2904 4.41448 16.3147 4.51483 16.312C4.61519 16.3094 4.713 16.2799 4.79812 16.2267L9 13.6005L13.2026 16.2267C13.2877 16.2797 13.3854 16.309 13.4857 16.3116C13.5859 16.3142 13.685 16.2899 13.7727 16.2412C13.8604 16.1926 13.9335 16.1214 13.9845 16.0351C14.0354 15.9487 14.0624 15.8503 14.0625 15.75V3.375C14.0625 3.07663 13.944 2.79048 13.733 2.5795C13.522 2.36853 13.2359 2.25 12.9375 2.25Z" fill="black"/>
            </svg>
          </a>
          {{-- Messages --}}
          <a href="{{ $frontendUrl ?? '/' }}/messages" class="relative flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">
            <svg width="18" height="18" viewBox="18 12 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.9603 24.0235H30.0398C30.6375 24.0235 31 23.727 31 23.2762C31 22.6587 30.3514 22.1028 29.8045 21.5532C29.3847 21.1271 29.2703 20.2501 29.2194 19.5399C29.1749 17.1684 28.5262 15.538 26.8347 14.9451C26.593 14.1361 25.938 13.5 24.9968 13.5C24.062 13.5 23.4006 14.1361 23.1654 14.9451C21.4738 15.538 20.8251 17.1684 20.7806 19.5399C20.7297 20.2501 20.6153 21.1271 20.1955 21.5532C19.6423 22.1028 19 22.6587 19 23.2762C19 23.727 19.3561 24.0235 19.9603 24.0235Z" fill="#0D0D0D"/>
            </svg>
            <span id="hi3dUnreadBadge" class="hidden absolute top-1 right-1 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full flex items-center justify-center" style="width: 16px; height: 16px;">0</span>
          </a>
          {{-- Profile --}}
          <a href="{{ $frontendUrl ?? '/' }}/dashboard/profile" class="flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">
            <svg width="15" height="12" viewBox="0 0 15 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M13.5937 2H7.96873L6.09374 0H1.40625C0.629589 0 0 0.671559 0 1.5V10.5C0 11.3285 0.629589 12 1.40625 12H13.5937C14.3704 12 15 11.3285 15 10.5V3.50002C15 2.67157 14.3704 2 13.5937 2Z" fill="#0D0D0D"/>
            </svg>
          </a>
        </div>
      @else
        {{-- Utilisateur non connecté --}}
        <div class="hidden lg:flex items-center gap-4">
          <button type="button" id="hi3dLoginBtn" class="transition-colors" style="width: 85px; height: 40px; padding: 12px 24px; gap: 5px; border-radius: 8px; background: #006EFF; font-family: 'Mona Sans', 'Inter', sans-serif; font-weight: 500; font-size: 14px; line-height: 16px; text-align: center; color: #FFFFFF; border: none; cursor: pointer;">Login</button>
        </div>
      @endif
    </div>
  </div>

  {{-- Mobile: Menu hamburger dans header (caché sur desktop) --}}
  <button class="lg:hidden p-2 -mr-2 text-gray-600 hover:text-gray-900 absolute right-4 top-1/2 -translate-y-1/2" id="hi3dHamburger" aria-label="Menu" style="transform: translateY(-50%);">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</header>

{{-- Menu hamburger mobile - Fixed bottom left --}}
<div class="fixed bottom-2 left-4 md:left-10 z-40" id="hi3dMobileMenuBtn">
  <button class="lg:hidden p-3 bg-white rounded-full shadow-lg text-gray-600 hover:text-gray-900" aria-label="Menu">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</div>

{{-- Become a Pro - Fixed bottom right --}}
<div class="fixed bottom-2 right-4 md:right-10 z-40">
  <a href="{{ $frontendUrl ?? '/' }}/subscription" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-black hover:bg-gray-800 rounded-full shadow-lg transition-colors" style="background: #000000;">
    Become a Pro
  </a>
</div>

{{-- Mobile menu overlay --}}
<div class="fixed inset-0 z-50 hidden" id="hi3dMobileOverlay">
  <div class="absolute inset-0 bg-black/50" id="hi3dMobileClose"></div>
  <div class="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-xl p-6 transform transition-transform" id="hi3dMobilePanel" style="transform: translateX(-100%);">
    <div class="flex justify-end mb-4">
      <button class="p-2 text-gray-500 hover:text-gray-700" id="hi3dMobileCloseBtn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    {{-- Menu mobile content - selon auth状态 --}}
    <div class="flex flex-col gap-4" id="hi3dMobileMenuContent">
      @if($isAuthenticated && $authUser)
        <div class="flex items-center gap-3 pb-4 border-b border-gray-200">
          <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 15 12" fill="#666">
              <path d="M13.5937 2H7.96873L6.09374 0H1.40625C0.629589 0 0 0.671559 0 1.5V10.5C0 11.3285 0.629589 12 1.40625 12H13.5937C14.3704 12 15 11.3285 15 10.5V3.50002C15 2.67157 14.3704 2 13.5937 2Z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900">{{ $authUser['name'] ?? 'User' }}</p>
            <p class="text-sm text-gray-500">{{ $authUser['email'] ?? '' }}</p>
          </div>
        </div>
        <a href="{{ $frontendUrl ?? '/' }}/favorite" class="w-full py-3 px-4 text-center font-medium text-gray-700 rounded-lg hover:bg-gray-50">Favorites</a>
        <a href="{{ $frontendUrl ?? '/' }}/messages" class="w-full py-3 px-4 text-center font-medium text-gray-700 rounded-lg hover:bg-gray-50">Messages</a>
        <a href="{{ $frontendUrl ?? '/' }}/dashboard/profile" class="w-full py-3 px-4 text-center font-medium text-gray-700 rounded-lg hover:bg-gray-50">Profile</a>
        <button type="button" id="hi3dMobileLogoutBtn" class="w-full py-3 px-4 text-center font-medium text-red-600 border border-red-600 rounded-lg hover:bg-red-50">Logout</button>
      @else
        <button type="button" id="hi3dMobileLoginBtn" class="w-full py-3 text-center font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Log in</button>
        <button type="button" id="hi3dMobileRegisterBtn" class="w-full py-3 text-center font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Sign up</button>
      @endif
    </div>
  </div>
</div>

{{-- JavaScript pour les interactions --}}
<script>
(function(){
  var searchInput = document.getElementById('hi3dSearchInput');
  var searchBtn = document.getElementById('hi3dSearchBtn');
  var typeServices = document.getElementById('hi3dTypeServices');
  var typeArtists = document.getElementById('hi3dTypeArtists');
  var suggestions = document.getElementById('hi3dSuggestions');
  var suggestionsList = document.getElementById('hi3dSuggestionsList');
  var loginBtn = document.getElementById('hi3dLoginBtn');
  var hamburger = document.getElementById('hi3dHamburger');
  var mobileMenuBtn = document.getElementById('hi3dMobileMenuBtn');
  var mobileOverlay = document.getElementById('hi3dMobileOverlay');
  var mobilePanel = document.getElementById('hi3dMobilePanel');
  var mobileCloseBtn = document.getElementById('hi3dMobileCloseBtn');
  var mobileClose = document.getElementById('hi3dMobileClose');
  var mobileLoginBtn = document.getElementById('hi3dMobileLoginBtn');
  var mobileRegisterBtn = document.getElementById('hi3dMobileRegisterBtn');
  var mobileLogoutBtn = document.getElementById('hi3dMobileLogoutBtn');
  
  var currentType = 'Services';
  var frontendUrl = '{{ $frontendUrl ?? "" }}';
  var backendUrl = '{{ $backendUrl ?? "" }}';
  var apiBaseUrl = '{{ $apiBaseUrl ?? "" }}';
  var blogUrl = '{{ $blogUrl ?? $frontendUrl ?? "" }}';
  var context = '{{ $context ?? "default" }}';

  function normalizeUrl(url) {
    if (url && url.indexOf('//') === -1) {
      return window.location.protocol + '//' + window.location.host;
    }
    return url;
  }

  frontendUrl = normalizeUrl(frontendUrl);
  backendUrl = normalizeUrl(backendUrl);
  apiBaseUrl = normalizeUrl(apiBaseUrl);
  blogUrl = normalizeUrl(blogUrl);

  function openMobileMenu() {
    mobileOverlay.classList.remove('hidden');
    mobilePanel.style.transform = 'translateX(0)';
  }
  
  function closeMobileMenu() {
    mobileOverlay.classList.add('hidden');
    mobilePanel.style.transform = 'translateX(-100%)';
  }

  function setActiveType(type) {
    currentType = type;
    if (typeServices && typeArtists) {
      typeServices.style.background = type === 'Services' ? '#F0F0F0' : '#FFFFFF';
      typeArtists.style.background = type === 'Artists' ? '#F0F0F0' : '#FFFFFF';
    }
  }

  function performSearch() {
    var term = searchInput ? searchInput.value.trim() : '';
    if (!term) return;
    
    if (context === 'blog' && blogUrl) {
      window.location.href = blogUrl + '/?s=' + encodeURIComponent(term);
    } else {
      var url = (frontendUrl || window.location.origin) + '/search-global?search=' + encodeURIComponent(term);
      if (currentType) {
        url += '&type=' + encodeURIComponent(currentType);
      }
      window.location.href = url;
    }
  }

  function fetchSuggestions(query) {
    if (!query || query.length < 2) {
      if (suggestions) suggestions.classList.add('hidden');
      return;
    }
    
    var searchType = currentType === 'Services' ? 'service_offers' : 'professional_profiles';
    var searchUrl = (backendUrl || window.location.origin) + '/api/search?q=' + encodeURIComponent(query) + '&types[]=' + searchType + '&per_page=5';
    
    fetch(searchUrl, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      var results = data.data?.data || data.data || [];
      if (!results || !results.length) {
        suggestionsList.innerHTML = '<p class="p-4 text-gray-500">No results</p>';
      } else {
        var html = '';
        results.forEach(function(item) {
          var name = item.name || item.title || item.professional?.name || 'Untitled';
          var href = item.service_slug ? (frontendUrl || window.location.origin) + '/service/' + item.service_slug : (frontendUrl || window.location.origin) + '/professional/' + item.slug;
          html += '<a href="' + href + '" class="block p-3 hover:bg-gray-50 border-b border-gray-100">' + name + '</a>';
        });
        suggestionsList.innerHTML = html;
      }
      suggestions.classList.remove('hidden');
    })
    .catch(function(err) {
      console.error('Search suggestions error:', err);
      suggestionsList.innerHTML = '<p class="p-4 text-gray-500">Error loading suggestions</p>';
      suggestions.classList.remove('hidden');
    });
  }

  function handleLogoutClick() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = (frontendUrl || window.location.origin) + '/';
  }

  function handleLoginClick() {
    window.location.href = (frontendUrl || window.location.origin) + '/login';
  }

  function handleRegisterClick() {
    window.location.href = (frontendUrl || window.location.origin) + '/register';
  }

  if (typeServices) typeServices.addEventListener('click', function() { setActiveType('Services'); });
  if (typeArtists) typeArtists.addEventListener('click', function() { setActiveType('Artists'); });
  if (searchBtn) searchBtn.addEventListener('click', performSearch);
  if (searchInput) {
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        performSearch();
      }
    });
    searchInput.addEventListener('input', function() {
      fetchSuggestions(this.value);
    });
    searchInput.addEventListener('focus', function() {
      if (this.value.length >= 2) fetchSuggestions(this.value);
    });
    searchInput.addEventListener('blur', function() {
      setTimeout(function() {
        if (suggestions) suggestions.classList.add('hidden');
      }, 200);
    });
  }

  if (loginBtn) loginBtn.addEventListener('click', handleLoginClick);
  if (hamburger) hamburger.addEventListener('click', openMobileMenu);
  if (mobileMenuBtn) mobileMenuBtn.querySelector('button').addEventListener('click', openMobileMenu);
  if (mobileCloseBtn) mobileCloseBtn.addEventListener('click', closeMobileMenu);
  if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
  if (mobileLoginBtn) mobileLoginBtn.addEventListener('click', handleLoginClick);
  if (mobileRegisterBtn) mobileRegisterBtn.addEventListener('click', handleRegisterClick);
  if (mobileLogoutBtn) {
    mobileLogoutBtn.addEventListener('click', handleLogoutClick);
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMobileMenu();
  });
})();
</script>