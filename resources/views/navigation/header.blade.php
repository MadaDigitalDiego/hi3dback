@php
$isAuthenticated = $isAuthenticated ?? false;
$authUser = $authUser ?? null;
$frontendUrl = $frontendUrl ?? rtrim(config('app.frontend_url', config('app.url')), '/');
$backendUrl = $backendUrl ?? rtrim(config('app.backend_url', config('app.url')), '/');
$apiBaseUrl = $apiBaseUrl ?? rtrim(config('app.api_base_url', $backendUrl), '/');
$blogUrl = !empty($blogUrl) ? rtrim($blogUrl, '/') : rtrim(config('app.blog_url', ''), '/');
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
        {{-- Utilisateur non connecté - loupe (mobile only) + login --}}
        <div class="flex items-center gap-3">
          {{-- Bouton loupe - visible sur mobile seulement --}}
          <button type="button" id="hi3dSearchOpenBtn" class="lg:hidden flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <g clip-path="url(#hi3dClipSearchBtn)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.5304 11.16L18 15.6937L15.75 18L11.1656 13.527C10.0094 14.2465 8.67434 14.627 7.3125 14.625C3.27375 14.625 0 11.3434 0 7.3125C0 3.27375 3.28163 0 7.3125 0C11.3512 0 14.625 3.28163 14.625 7.3125C14.627 8.67212 14.2478 10.0051 13.5304 11.16ZM2.24662 7.2585C2.24662 10.0485 4.51238 12.321 7.30913 12.321C10.0991 12.321 12.3716 10.0541 12.3716 7.2585C12.3716 4.4685 10.1047 2.196 7.30913 2.196C4.51912 2.196 2.24662 4.46175 2.24662 7.2585Z" fill="black"/>
                <circle cx="6" cy="6" r="2" fill="#0D0D0D"/>
              </g>
              <defs>
                <clipPath id="hi3dClipSearchBtn">
                  <rect width="18" height="18" fill="white"/>
                </clipPath>
              </defs>
            </svg>
          </button>
          <button type="button" id="hi3dLoginBtn" class="transition-colors" style="width: 85px; height: 40px; padding: 12px 24px; gap: 5px; border-radius: 8px; background: #006EFF; font-family: 'Mona Sans', 'Inter', sans-serif; font-weight: 500; font-size: 14px; line-height: 16px; text-align: center; color: #FFFFFF; border: none; cursor: pointer;">Login</button>
        </div>
      @endif
    </div>
  </div>
</header>

{{-- Mobile Search Modal - Appears when clicking loupe --}}
<div class="fixed inset-0 px-3 pt-3 bg-black bg-opacity-50 flex flex-col items-center hidden" id="hi3dMobileSearchModal" style="z-index: 2147483647; height: 100dvh; overflow: hidden;">
  <div class="w-full max-w-[480px] flex flex-col flex-1 min-h-0">
    <form id="hi3dMobileSearchForm" class="relative w-full flex flex-col flex-1 min-h-0">
      <div class="flex flex-col bg-white rounded-xl px-2 py-2" style="box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.28);">
        <div class="flex items-center gap-0">
          <button type="button" id="hi3dMobileSearchCloseBtn" class="flex items-center justify-center w-6 h-6 bg-transparent flex-shrink-0 mr-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="32" viewBox="0 0 20 20" fill="none">
              <path d="M13 5L5 10L13 15V5Z" fill="#4A4A4A"/>
            </svg>
          </button>
          <div class="relative flex-1 min-w-0">
            <input type="text" id="hi3dMobileSearchInput" placeholder="Search" class="w-full bg-[#E6E6E6] rounded-md pl-4 pr-10 text-[16px] placeholder:text-[#8E8E8E] text-[#0D0C22] focus:outline-none" style="font-family: Inter, sans-serif; font-weight: 400; line-height: 20px; height: 40px;" />
          </div>
        </div>
        <div class="flex items-center justify-end pt-2">
          <div class="flex items-center bg-white rounded-md border border-[#E5E5E5] p-1">
            <button type="button" id="hi3dTypeServicesMobile" class="px-3 py-1 rounded-md text-[12px] font-medium bg-[#EDEDED] text-[#333333]">Services</button>
            <button type="button" id="hi3dTypeArtistsMobile" class="px-3 py-1 rounded-md text-[12px] font-medium bg-transparent text-[#8E8E8E]">Artists</button>
          </div>
        </div>
      </div>
      <div id="hi3dMobileSearchResults" class="relative w-full flex-1 min-h-0 overflow-y-auto mt-2"></div>
    </form>
  </div>
</div>

{{-- Menu hamburger - Fixed bottom left - visible sur desktop et mobile --}}
<div class="fixed bottom-2 left-4 md:left-10 z-40" id="hi3dMobileMenuBtn">
  <button class="p-3 rounded-lg shadow-lg text-gray-600 hover:text-gray-900" style="width: 50px; height: 40px; background: #F0F0F0;" aria-label="Menu">
    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M11.5714 10.2857H0.428571C0.314907 10.2857 0.205898 10.3309 0.125526 10.4112C0.0451529 10.4916 0 10.6006 0 10.7143L0 11.5714C0 11.6851 0.0451529 11.7941 0.125526 11.8745C0.205898 11.9548 0.314907 12 0.428571 12H11.5714C11.6851 12 11.7941 11.9548 11.8745 11.8745C11.9548 11.7941 12 11.6851 12 11.5714V10.7143C12 10.6006 11.9548 10.4916 11.8745 10.4112C11.7941 10.3309 11.6851 10.2857 11.5714 10.2857ZM11.5714 6.85714H0.428571C0.314907 6.85714 0.205898 6.9023 0.125526 6.98267C0.0451529 7.06304 0 7.17205 0 7.28571L0 8.14286C0 8.25652 0.0451529 8.36553 0.125526 8.4459C0.205898 8.52628 0.314907 8.57143 0.428571 8.57143H11.5714C11.6851 8.57143 11.7941 8.52628 11.8745 8.4459C11.9548 8.36553 12 8.25652 12 8.14286V7.28571C12 7.17205 11.9548 7.06304 11.8745 6.98267C11.7941 6.9023 11.6851 6.85714 11.5714 6.85714ZM11.5714 3.42857H0.428571C0.314907 3.42857 0.205898 3.47372 0.125526 3.5541C0.0451529 3.63447 0 3.74348 0 3.85714L0 4.71429C0 4.82795 0.0451529 4.93696 0.125526 5.01733C0.205898 5.0977 0.314907 5.14286 0.428571 5.14286H11.5714C11.6851 5.14286 11.7941 5.0977 11.8745 5.01733C11.9548 4.93696 12 4.82795 12 4.71429V3.85714C12 3.74348 11.9548 3.63447 11.8745 3.5541C11.7941 3.47372 11.6851 3.42857 11.5714 3.42857ZM11.5714 0H0.428571C0.314907 0 0.205898 0.0451529 0.125526 0.125526C0.0451529 0.205898 0 0.314907 0 0.428571L0 1.28571C0 1.39938 0.0451529 1.50839 0.125526 1.58876C0.205898 1.66913 0.314907 1.71429 0.428571 1.71429H11.5714C11.6851 1.71429 11.7941 1.66913 11.8745 1.58876C11.9548 1.50839 12 1.39938 12 1.28571V0.428571C12 0.314907 11.9548 0.205898 11.8745 0.125526C11.7941 0.0451529 11.6851 0 11.5714 0Z" fill="#0D0D0D"/>
    </svg>
  </button>
</div>

{{-- Become a Pro - Fixed bottom right --}}
<div class="fixed bottom-2 right-4 md:right-10 z-40">
  <a href="{{ $frontendUrl ?? '/' }}/subscription" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-black hover:bg-gray-800 rounded-lg shadow-lg transition-colors" style="background: #000000; border-radius: 8px;">
    Become a Pro
  </a>
</div>

{{-- Mobile menu overlay - SidebarMenu (glisse depuis la gauche) --}}
<div class="fixed inset-0 hidden" id="hi3dMobileOverlay" style="z-index: 99999;">
  <div class="absolute inset-0 bg-black bg-opacity-50" id="hi3dMobileClose"></div>
  <div class="absolute left-0 top-0 bottom-0 w-full max-w-sm bg-gray-100 shadow-xl flex flex-col transform transition-transform" id="hi3dMobilePanel" style="z-index: 100000; max-width: 380px; transform: translateX(-100%);">
    
    {{-- Header avec user info ou close button --}}
    <div class="p-4 border-b">
      @if($isAuthenticated && $authUser)
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 15 12" fill="#666">
              <path d="M13.5937 2H7.96873L6.09374 0H1.40625C0.629589 0 0 0.671559 0 1.5V10.5C0 11.3285 0.629589 12 1.40625 12H13.5937C14.3704 12 15 11.3285 15 10.5V3.50002C15 2.67157 14.3704 2 13.5937 2Z"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px;">{{ $authUser['name'] ?? 'User' }}</p>
            <p class="text-sm text-gray-500" style="font-family: 'Mona Sans', sans-serif; font-size: 12px;">{{ $authUser['email'] ?? '' }}</p>
          </div>
        </div>
      @else
        <div class="flex justify-end">
          <button class="p-2 rounded-full hover:bg-neutral-100" id="hi3dMobileCloseBtn">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      @endif
    </div>

    {{-- Menu content scrollable --}}
    <div class="flex-1 overflow-y-auto" style="padding: 16px 0;">
      
      {{-- Connected user: Dashboard menu --}}
      @if($isAuthenticated && $authUser)
        <div style="padding: 0 16px;">
          <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200">
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
              <svg width="16" height="16" viewBox="0 0 15 12" fill="#666">
                <path d="M13.5937 2H7.96873L6.09374 0H1.40625C0.629589 0 0 0.671559 0 1.5V10.5C0 11.3285 0.629589 12 1.40625 12H13.5937C14.3704 12 15 11.3285 15 10.5V3.50002C15 2.67157 14.3704 2 13.5937 2Z"/>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px;">{{ $authUser['name'] ?? 'User' }}</p>
              <p class="text-sm text-gray-500" style="font-family: 'Mona Sans', sans-serif; font-size: 12px;">{{ $authUser['email'] ?? '' }}</p>
            </div>
          </div>
          
          {{-- Dashboard items --}}
          <a href="{{ $frontendUrl ?? '/' }}/favorite" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M12.9375 2.25H5.0625C4.76413 2.25 4.47798 2.36853 4.267 2.5795C4.05603 2.79048 3.9375 3.07663 3.9375 3.375V15.75C3.9375 3.07663 4.05603 2.79048 4.267 2.5795L12.9375 2.25Z" fill="black"/></svg>
            </span>
            Favorites
          </a>
          <a href="{{ $frontendUrl ?? '/' }}/messages" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 12" fill="none"><path d="M16.9603 12.0235H1.0398C0.442128 12.0235 0 10.727 0 10.2762C0 9.65875 0.648627 9.10283 1.1955 8.55325C1.61533 8.12712 1.7297 7.25012 1.7806 6.5399C1.82508 4.16837 2.47379 2.538 4.16532 1.94512C4.40699 1.13612 5.062 0.5 6.00325 0.5C6.938 0.5 7.59938 1.13612 7.83457 1.94512C9.52604 2.538 10.1747 4.16837 10.2192 6.5399C10.2701 7.25012 10.3845 8.12712 10.8043 8.55325C11.3575 9.10283 12 9.65875 12 10.2762C12 10.727 11.6439 12.0235 11.0398 12.0235H16.9603Z" fill="black"/></svg>
            </span>
            Messages
          </a>
          <a href="{{ $frontendUrl ?? '/' }}/dashboard/projects" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2.25 3.375C2.25 2.87609 2.44719 2.39881 2.79553 2.05237C3.14387 1.70593 3.6117 1.5 4.125 1.5H13.875C14.3883 1.5 14.8561 1.70593 15.2045 2.05237C15.5528 2.39881 15.75 2.87609 15.75 3.375V14.625C15.75 15.1239 15.5528 15.6012 15.2045 15.9476C14.8561 16.2941 14.3883 16.5 13.875 16.5H4.125C3.6117 16.5 3.14387 16.2941 2.79553 15.9476C2.44719 15.6012 2.25 15.1239 2.25 14.625V3.375Z" fill="black"/></svg>
            </span>
            Projects
          </a>
          <a href="{{ $frontendUrl ?? '/' }}/dashboard/services" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 0L11.1213 6.87868L18 9L11.1213 11.1213L9 18L6.87868 11.1213L0 9L6.87868 6.87868L9 0Z" fill="black"/></svg>
            </span>
            Services
          </a>
          <a href="{{ $frontendUrl ?? '/' }}/dashboard/subscription" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 0C13.9706 0 18 4.02944 18 9C18 13.9706 13.9706 18 9 18C4.02944 18 0 13.9706 0 9C0 4.02944 4.02944 0 9 0Z" fill="black" fill-opacity="0.2"/><path d="M9 4V9L12.5 11.5" stroke="black" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            Subscription
          </a>
          <a href="{{ $frontendUrl ?? '/' }}/dashboard/settings" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 5.625C10.8565 5.625 12.375 7.1435 12.375 9C12.375 10.8565 10.8565 12.375 9 12.375C7.1435 12.375 5.625 10.8565 5.625 9C5.625 7.1435 7.1435 5.625 9 5.625Z" fill="black"/><path d="M14.8359 11.8125C14.5623 12.0754 14.2372 12.2654 13.8842 12.3674C13.5313 12.4695 13.1603 12.4803 12.8012 12.3989C12.4422 12.3175 12.1061 12.1464 11.8193 11.8996C11.5325 11.6527 11.3032 11.3376 11.1531 10.9799C11.003 10.6221 10.9375 10.2339 10.962 9.84625C10.9864 9.4586 11.0996 9.08171 11.2933 8.74438C11.487 8.40705 11.7562 8.11968 12.0827 7.90312C12.4092 7.68656 12.7827 7.5467 13.1749 7.49294C13.5671 7.43918 13.9658 7.47332 14.3399 7.59267C14.7141 7.71203 15.0528 7.91408 15.3307 8.18675L15.3731 8.22912C15.5348 8.39075 15.6601 8.58691 15.7409 8.80389C15.8216 9.02087 15.8561 9.25303 15.8416 9.48346C15.8271 9.71389 15.7631 9.93708 15.6544 10.1393C15.5457 10.3415 15.395 10.5178 15.2126 10.6559C15.0302 10.794 14.8204 10.8899 14.5969 10.9367C14.3734 10.9834 14.1415 10.9783 13.9199 10.9217C13.6982 10.8651 13.4919 10.7587 13.3146 10.6101C13.1374 10.4615 13.0019 10.2749 12.9188 10.0644C12.8357 9.85384 12.8071 9.62583 12.8356 9.40068C12.864 9.17553 12.9491 8.95893 13.0849 8.76576C13.2206 8.57259 13.4027 8.408 13.6179 8.28412L13.6603 8.24175C13.4014 8.016 13.1938 7.72862 13.0568 7.40412C12.9199 7.07962 12.8582 6.72802 12.8777 6.37607C12.8972 6.02412 12.9974 5.68065 13.1702 5.37456C13.3431 5.06848 13.5843 4.80878 13.8779 4.61431C14.1715 4.41985 14.5097 4.29588 14.8686 4.25099C15.2276 4.2061 15.5967 4.24146 15.9507 4.35434C16.3047 4.46721 16.6341 4.65508 16.914 4.90425C17.194 5.15341 17.4181 5.46735 17.5705 5.82628C17.7228 6.18521 17.8001 6.57967 17.7972 6.97952C17.7943 7.37938 17.7104 7.77263 17.5521 8.12952C17.3938 8.48641 17.1644 8.79768 16.8799 9.04356L16.8375 9.08594C16.6758 9.24756 16.5506 9.44359 16.4698 9.66036C16.389 9.87712 16.3544 10.1091 16.3688 10.3394C16.3832 10.5697 16.4462 10.7928 16.5547 10.9949C16.6632 11.1971 16.8138 11.3734 16.9961 11.5116C17.1784 11.6497 17.3881 11.7457 17.6117 11.7925C17.8353 11.8393 18.0673 11.8343 18.2891 11.7778C18.5108 11.7213 18.7172 11.6149 18.8945 11.4664C19.0718 11.3179 19.2073 11.1314 19.2905 10.9209C19.3736 10.7104 19.4022 10.4825 19.3737 10.2574C19.3452 10.0323 19.2601 9.8157 19.1242 9.62253C18.9884 9.42935 18.8062 9.26473 18.5909 9.14077L18.5485 9.0984C18.8075 8.8725 19.0152 8.58506 19.1523 8.26062C19.2893 7.93618 19.3511 7.58467 19.3316 7.23278C19.3121 6.88088 19.2119 6.53748 19.0391 6.23146C18.8663 5.92544 18.6251 5.66578 18.3315 5.47136C18.0379 5.27695 17.6997 5.15301 17.3408 5.10813C16.9819 5.06325 16.6128 5.09861 16.2588 5.21149C15.9048 5.32437 15.5754 5.51225 15.2955 5.76143C15.0155 6.01061 14.7914 6.32456 14.6391 6.6835C14.4867 7.04244 14.4094 7.4369 14.4123 7.83677C14.4152 8.23663 14.4991 8.62989 14.6575 8.98679C14.8158 9.34368 15.0452 9.65496 15.3297 9.90084L15.3721 9.94321C15.5338 10.1048 15.6591 10.3009 15.7399 10.5178C15.8206 10.7347 15.856 10.9667 15.8415 11.1971C15.827 11.4275 15.7631 11.6506 15.6544 11.8528C15.5457 12.055 15.395 12.2313 15.2126 12.3694C15.0302 12.5075 14.8204 12.6034 14.5969 12.6502C14.3734 12.6969 14.1415 12.6919 13.9199 12.6353C13.6982 12.5787 13.4919 12.4723 13.3146 12.3237C13.1374 12.1751 13.0019 11.9886 12.9188 11.778C12.8357 11.5675 12.8071 11.3395 12.8356 11.1143C12.864 10.8892 12.9491 10.6726 13.0849 10.4794C13.2206 10.2862 13.4027 10.1216 13.6179 9.99773L13.6603 9.95535C13.4014 9.72965 13.1938 9.44227 13.0568 9.11777C12.9199 8.79327 12.8582 8.44167 12.8777 8.08972C12.8972 7.73777 12.9974 7.3943 13.1702 7.08822C13.3431 6.78214 13.5843 6.52244 13.8779 6.32797C14.1715 6.13351 14.5097 6.00954 14.8686 5.96465C15.2276 5.91976 15.5967 5.95512 15.9507 6.068C16.3047 6.18087 16.6341 6.36875 16.914 6.61793C17.194 6.8671 17.4181 7.18104 17.5705 7.53998C17.7228 7.89891 17.8001 8.29338 17.7972 8.69323C17.7943 9.09309 17.7104 9.48634 17.5521 9.84324C17.3938 10.2001 17.1644 10.5114 16.8799 10.7573L16.8375 10.7997C16.6758 10.9613 16.5506 11.1573 16.4698 11.3741C16.389 11.5909 16.3544 11.8229 16.3688 12.0533C16.3832 12.2837 16.4462 12.5069 16.5547 12.709C16.6632 12.9112 16.8138 13.0875 16.9961 13.2257C17.1784 13.3638 17.3881 13.4598 17.6117 13.5065C17.8353 13.5533 18.0673 13.5483 18.2891 13.4917C18.5108 13.4352 18.7172 13.3288 18.8945 13.1802C19.0718 13.0316 19.2072 12.8451 19.2904 12.6345C19.3735 12.424 19.4021 12.196 19.3737 11.9708C19.3452 11.7457 19.2601 11.5291 19.1242 11.3359C18.9884 11.1427 18.8062 10.9781 18.5909 10.8542Z" fill="black"/></svg>
            </span>
            Settings
          </a>
          <button type="button" id="hi3dMobileLogoutBtn" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #DC2626;">
            <span style="width: 24px; margin-right: 16px;">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M7.5 17.25C7.5 17.4489 7.42098 17.6397 7.28033 17.7803C7.13968 17.921 6.94891 18 6.75 18H0.75C0.551088 18 0.360322 17.921 0.21967 17.7803C0.0790178 17.6397 0 17.4489 0 17.25V0.75C0 0.551088 0.0790178 0.360322 0.21967 0.21967C0.360322 0.0790178 0.551088 0 0.75 0H6.75C6.94891 0 7.13968 0.0790178 7.28033 0.21967C7.42098 0.360322 7.5 0.551088 7.5 0.75C7.5 0.948912 7.42098 1.13968 7.28033 1.28033C7.13968 1.42098 6.94891 1.5 6.75 1.5H1.5V16.5H6.75C6.94891 16.5 7.13968 16.579 7.28033 16.7197C7.42098 16.8603 7.5 17.0511 7.5 17.25ZM17.7806 8.46937L14.0306 4.71937C13.9257 4.61437 13.792 4.54284 13.6465 4.51385C13.5009 4.48487 13.35 4.49972 13.2129 4.55653C13.0758 4.61335 12.9586 4.70957 12.8762 4.83301C12.7938 4.95646 12.7499 5.10158 12.75 5.25V8.25H6.75C6.55109 8.25 6.36032 8.32902 6.21967 8.46967C6.07902 8.61032 6 8.80109 6 9C6 9.19891 6.07902 9.38968 6.21967 9.53033C6.36032 9.67098 6.55109 9.75 6.75 9.75H12.75V12.75C12.7499 12.8984 12.7938 13.0435 12.8762 13.167C12.9586 13.2904 13.0758 13.3867 13.2129 13.4435C13.35 13.5003 13.5009 13.5151 13.6465 13.4861C13.792 13.4572 13.9257 13.3856 14.0306 13.2806L17.7806 9.53063C17.8504 9.46097 17.9057 9.37825 17.9434 9.2872C17.9812 9.19616 18.0006 9.09856 18.0006 9C18.0006 8.90144 17.9812 8.80384 17.9434 8.7128C17.9057 8.62175 17.8504 8.53903 17.7806 8.46937Z" fill="#DC2626"/></svg>
            </span>
            Log out
          </button>
        </div>
      @endif

      {{-- Section Other --}}
      <div style="margin-top: 16px; padding: 8px 16px;">
        <h3 style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 20px; line-height: 20px; color: #0D0D0D; margin-bottom: 8px;">
          Other
        </h3>
        
        <a href="{{ $frontendUrl ?? '/' }}/subscription" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
          <span style="width: 24px; margin-right: 16px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 0C13.9706 0 18 4.02944 18 9C18 13.9706 13.9706 18 9 18C4.02944 18 0 13.9706 0 9C0 4.02944 4.02944 0 9 0Z" fill="#0D0D0D"/></svg>
          </span>
          Become a Pro
        </a>
        
        <a href="{{ $blogUrl ?? 'https://dev2.hi-3d.com/blog' }}" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
          <span style="width: 24px; margin-right: 16px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2.25 3.375C2.25 2.87609 2.44719 2.39881 2.79553 2.05237C3.14387 1.70593 3.6117 1.5 4.125 1.5H13.875C14.3883 1.5 14.8561 1.70593 15.2045 2.05237C15.5528 2.39881 15.75 2.87609 15.75 3.375V14.625C15.75 15.1239 15.5528 15.6012 15.2045 15.9476C14.8561 16.2941 14.3883 16.5 13.875 16.5H4.125C3.6117 16.5 3.14387 16.2941 2.79553 15.9476C2.44719 15.6012 2.25 15.1239 2.25 14.625V3.375Z" fill="#0D0D0D"/></svg>
          </span>
          Blog
        </a>
        
        <a href="{{ $frontendUrl ?? '/' }}/categories" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
          <span style="width: 24px; margin-right: 16px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M5.625 0L9 6.75L12.375 0H14.625L9 9L3.375 0H5.625ZM0 11.25L3.375 18L9 9L0 11.25ZM14.625 18L9 9L14.625 18Z" fill="#0D0D0D"/></svg>
          </span>
          Categories
        </a>
        
        <a href="{{ $frontendUrl ?? '/' }}/about" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
          <span style="width: 24px; margin-right: 16px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="7" stroke="#0D0D0D" stroke-width="2" fill="none"/><path d="M9 5V9M9 12H9.01" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"/></svg>
          </span>
          About
        </a>
        
        <a href="{{ $frontendUrl ?? '/' }}/careers" class="flex items-center w-full text-left px-4 py-3" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 14px; color: #0D0D0D;">
          <span style="width: 24px; margin-right: 16px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 0L11.1213 6.87868L18 9L11.1213 11.1213L9 18L6.87868 11.1213L0 9L6.87868 6.87868L9 0Z" fill="#0D0D0D"/></svg>
          </span>
          Careers
        </a>
      </div>
    </div>

    {{-- Footer: Scroll down avec logo et réseaux sociaux --}}
    <div style="padding: 8px 16px 16px; border-top: 1px solid #CCCCCC;">
      <button type="button" id="hi3dScrollDownBtn" class="flex items-center gap-2 py-2 px-2 hover:bg-gray-100 rounded transition-colors" style="font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; color: #000000; margin-bottom: 8px;">
        Scroll down
        <svg width="12" height="8" viewBox="0 0 12 8" fill="none"><path d="M1 1L5.99963 6L11 1" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      
      {{-- Logo HI3D --}}
      <div style="margin-bottom: 16px;">
        <svg width="32" height="24" viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M11.1984 6.21589C12.8811 6.21589 14.2452 4.87803 14.2452 3.2277C14.2452 1.57736 12.8811 0.239502 11.1984 0.239502C9.51574 0.239502 8.15164 1.57736 8.15164 3.2277C8.15164 4.87803 9.51574 6.21589 11.1984 6.21589Z" fill="#0D0D0D"/>
          <path d="M3.88614 16.301C5.56884 16.301 6.93293 17.6389 6.93293 19.2892V21.0076C6.93293 21.6111 6.75046 22.1729 6.43668 22.6428C5.89252 23.4575 4.95341 23.9958 3.88614 23.9958C2.81887 23.9958 1.87977 23.4575 1.3356 22.6428C1.02182 22.1729 0.839355 21.6111 0.839355 21.0076V19.2892V16.3614V15.0454C1.09194 15.2982 1.37697 15.5197 1.68783 15.7034C2.32982 16.0828 3.08199 16.301 3.88614 16.301Z" fill="#0D0D0D"/>
          <path d="M11.1994 23.9958C9.51666 23.9958 8.15164 22.6579 8.15164 21.0076V19.2892C8.15164 18.1494 7.68682 17.116 6.93293 16.3614C6.68035 16.1086 6.39532 15.8871 6.08445 15.7034C5.44247 15.324 4.69029 15.1058 3.88614 15.1058C2.20345 15.1058 0.839355 13.7679 0.839355 12.1176C0.839355 10.4672 2.20345 9.12938 3.88614 9.12938C4.94649 9.12938 5.96963 9.28563 6.93293 9.57593C7.08496 9.62175 7.23549 9.6709 7.38445 9.72331C7.64518 9.81504 7.90107 9.91673 8.15164 10.0279C11.7451 11.6224 14.2452 15.1695 14.2452 19.2892V21.0076C14.2452 21.7094 13.9985 22.3547 13.5856 22.8646C13.0623 23.5107 12.2721 23.9395 11.3785 23.9906C11.3767 23.9906 11.3353 23.9918 11.2927 23.9931L11.2901 23.9931C11.2666 23.9938 11.2428 23.9945 11.2254 23.995L11.2057 23.9956L11.2013 23.9957L11.1994 23.9958Z" fill="#0D0D0D"/>
          <path d="M3.88614 0.239502C2.20345 0.239502 0.839355 1.57736 0.839355 3.2277V8.35111V9.18975C1.51129 8.51724 2.41285 8.06625 3.41959 7.95884C3.57282 7.94249 3.72848 7.9341 3.88614 7.9341C4.03777 7.9341 4.18755 7.94186 4.3351 7.957C5.23082 7.98939 6.10051 8.12171 6.93293 8.34293V3.2277C6.93293 1.57736 5.56884 0.239502 3.88614 0.239502Z" fill="#0D0D0D"/>
          <path d="M21.1453 23.0384C20.9497 23.3349 20.6887 23.5701 20.3656 23.7429L20.3643 23.7435L20.3631 23.7443C20.0377 23.9126 19.6811 23.9956 19.2972 23.9956C18.8676 23.9956 18.478 23.9082 18.1342 23.728L18.133 23.7273L18.1317 23.7267C17.792 23.5429 17.5219 23.2934 17.326 22.979L17.3252 22.9777C17.1284 22.656 17.0312 22.2972 17.0312 21.9066C17.0313 21.7454 17.0789 21.5948 17.1914 21.4759L17.1917 21.4755C17.3026 21.359 17.4448 21.2924 17.6094 21.2924C17.7765 21.2924 17.9227 21.3572 18.0323 21.4812C18.1414 21.5993 18.1876 21.7477 18.1876 21.9066C18.1876 22.0829 18.2329 22.2442 18.3243 22.3944L18.3608 22.4485C18.4492 22.5713 18.5632 22.6734 18.7054 22.755C18.8628 22.8403 19.0424 22.8849 19.2491 22.8849C19.5613 22.8849 19.7897 22.8024 19.9561 22.6556L19.9863 22.6273C20.1331 22.4823 20.2146 22.2812 20.2146 22.0008C20.2146 21.8115 20.1735 21.6499 20.0963 21.5111L20.0957 21.51L20.0951 21.5088C20.0152 21.3609 19.9087 21.2466 19.775 21.162C19.6457 21.0802 19.499 21.0381 19.3291 21.0381C19.1583 21.0381 19.0055 20.9787 18.8845 20.8601L18.882 20.8576L18.8794 20.8549C18.7656 20.7354 18.7109 20.5871 18.7109 20.4239C18.7109 20.2608 18.7659 20.112 18.8845 19.9957C19.0055 19.877 19.1583 19.8177 19.3291 19.8176C19.441 19.8176 19.5503 19.7906 19.6594 19.7333C19.7701 19.67 19.8604 19.5893 19.9322 19.4903C19.9977 19.3951 20.0307 19.2899 20.0307 19.1687C20.0307 18.9955 19.9692 18.8565 19.8411 18.7388C19.715 18.6228 19.5503 18.5592 19.3291 18.5592C19.1555 18.5592 19 18.5924 18.8597 18.6567C18.723 18.7217 18.6179 18.8056 18.5389 18.9065C18.4697 19.0015 18.4357 19.1052 18.4357 19.2237C18.4356 19.3856 18.3879 19.5351 18.2846 19.6598L18.2803 19.6648C18.1707 19.7888 18.0244 19.8536 17.8573 19.8536C17.6977 19.8535 17.5569 19.7934 17.4447 19.6833L17.4395 19.6782L17.4345 19.6725C17.3284 19.5524 17.2792 19.4061 17.2792 19.2472C17.2792 18.9062 17.3697 18.5947 17.5532 18.3187C17.7342 18.0465 17.9811 17.8349 18.2884 17.6836C18.5983 17.5258 18.9413 17.4485 19.3131 17.4485C19.65 17.4485 19.9644 17.5408 20.2335 17.7178L20.2342 17.7184C20.5095 17.8991 20.732 18.1478 20.8897 18.4453L20.8907 18.4475C21.0424 18.7352 21.1219 19.0531 21.1219 19.3824C21.1219 19.7368 21.029 20.0784 20.8561 20.3858C20.6833 20.6932 20.4386 20.9551 20.1461 21.1471C19.8536 21.3391 19.5236 21.456 19.1819 21.4877C18.8402 21.5194 18.5001 21.4647 18.1915 21.3286L18.1907 21.3279C17.8978 21.1989 17.6376 21.0069 17.4244 20.7648C17.2112 20.5227 17.0509 20.2379 16.9551 19.9274C16.8593 19.6169 16.8305 19.2899 16.8707 18.9677C16.9109 18.6455 17.0191 18.3369 17.1885 18.0606L17.2443 17.9671C17.4121 17.6933 17.6378 17.4631 17.9049 17.2929C18.1721 17.1227 18.4726 17.0167 18.7871 16.9817C19.1016 16.9467 19.4199 16.9835 19.7183 17.0889C20.0167 17.1942 20.2866 17.3653 20.5076 17.5897C20.7286 17.8142 20.8953 18.0864 20.9972 18.3877C21.0991 18.689 21.1333 19.0105 21.0971 19.3259C21.0609 19.6413 20.9553 19.9438 20.7894 20.2111C20.6236 20.4783 20.4018 20.7036 20.1392 20.8714L20.0948 20.8989C19.8562 21.0561 19.5859 21.1646 19.3036 21.2153C19.0213 21.266 18.7353 21.2571 18.4623 21.1895C18.1894 21.1219 17.9374 21.0013 17.7246 20.8357C17.5119 20.6701 17.3433 20.463 17.2308 20.2283L17.1881 20.1536C16.9817 19.7742 16.8606 19.3495 16.8372 18.9164C16.8139 18.4833 16.8897 18.0531 17.0594 17.6609C17.2292 17.2687 17.4877 16.9238 17.8158 16.6526C18.144 16.3814 18.5339 16.1911 18.9585 16.0953C19.3831 15.9994 19.8218 16.0003 20.2459 16.0979C20.6699 16.1955 21.0588 16.3876 21.3856 16.6604C21.7124 16.9332 21.9691 17.2794 22.137 17.6735C22.3048 18.0676 22.3793 18.4984 22.3544 18.9316C22.3295 19.3648 22.2057 19.7879 21.9933 20.1695L21.9511 20.2441C21.7514 20.6091 21.4919 20.9393 21.1843 21.2199C20.8767 21.5006 20.5261 21.7259 20.1496 21.8842C20.0577 21.9282 19.9626 21.9654 19.8651 21.9951L21.1453 23.0384Z" fill="#0D0D0D"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M24.4133 17.4564C24.8958 17.4564 25.3395 17.5362 25.7415 17.6995L25.7414 17.6997C26.1453 17.8564 26.4973 18.0834 26.795 18.381C27.0928 18.6735 27.3211 19.0188 27.4803 19.4148C27.6405 19.813 27.719 20.2497 27.719 20.722C27.719 21.1945 27.6405 21.6334 27.4807 22.0363L27.4803 22.0372C27.321 22.4335 27.0925 22.7813 26.795 23.0787L26.7943 23.0794L26.7935 23.08C26.4956 23.3722 26.1439 23.599 25.7406 23.7606L25.7391 23.7612C25.3378 23.9186 24.8949 23.9956 24.4133 23.9956H22.9656C22.7948 23.9956 22.6419 23.9362 22.5209 23.8175C22.3999 23.6988 22.3393 23.5489 22.3392 23.3814V18.0705C22.3392 17.9031 22.3966 17.7511 22.5209 17.6345C22.6419 17.5158 22.7948 17.4564 22.9656 17.4564H24.4133ZM26.2287 19.6058C26.4064 19.9214 26.4985 20.2914 26.4985 20.722C26.4985 21.1464 26.4069 21.5167 26.2287 21.8383C26.0492 22.1571 25.8032 22.4075 25.4888 22.5926C25.1842 22.7697 24.828 22.8613 24.4133 22.8614H23.5918V18.5905H24.4133C24.8284 18.5905 25.185 18.6824 25.4897 18.8598L25.4909 18.8605C25.8042 19.0401 26.0494 19.2874 26.2287 19.6058Z" fill="#0D0D0D"/>
        </svg>
      </div>
      
      {{-- Social links --}}
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <a href="https://facebook.com" target="_blank" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 4px; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; color: #0D0D0D;">
          Facebook
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M10.2578 7.875L10.6466 5.34133H8.21551V3.69715C8.21551 3.00398 8.55512 2.32832 9.64395 2.32832H10.7492V0.171172C10.7492 0.171172 9.74621 0 8.78727 0C6.78516 0 5.47648 1.21352 5.47648 3.41031V5.34133H3.25098V7.875H5.47648V14H8.21551V7.875H10.2578Z" fill="#666666"/></svg>
        </a>
        <a href="https://twitter.com" target="_blank" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 4px; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; color: #0D0D0D;">
          Twitter
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M12.5609 4.14844C12.5698 4.2728 12.5698 4.39719 12.5698 4.52154C12.5698 8.31467 9.68275 12.6852 4.40609 12.6852C2.78045 12.6852 1.27031 12.2144 0 11.3972C0.230973 11.4238 0.453031 11.4327 0.692891 11.4327C2.03424 11.4327 3.26903 10.9797 4.25507 10.2068C2.99365 10.1802 1.93654 9.35403 1.57232 8.21697C1.75 8.2436 1.92765 8.26138 2.11422 8.26138C2.37182 8.26138 2.62946 8.22583 2.86929 8.16368C1.55457 7.89716 0.568504 6.74235 0.568504 5.34768V5.31216C0.950469 5.52536 1.39467 5.65861 1.86545 5.67635C1.0926 5.16111 0.586277 4.28169 0.586277 3.28676C0.586277 2.75377 0.728383 2.26519 0.977129 1.83879C2.38957 3.57991 4.51268 4.71694 6.89336 4.84133C6.84895 4.62813 6.82229 4.40607 6.82229 4.18399C6.82229 2.60275 8.10149 1.3147 9.69158 1.3147C10.5177 1.3147 11.2639 1.66114 11.788 2.22079C12.4365 2.09643 13.0583 1.85657 13.6091 1.5279C13.3959 2.19415 12.9428 2.7538 12.3477 3.1091C12.9251 3.04695 13.4847 2.88702 13.9999 2.66496C13.6091 3.23346 13.1205 3.73979 12.5609 4.14844Z" fill="#666666"/></svg>
        </a>
        <a href="https://instagram.com" target="_blank" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 4px; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; color: #0D0D0D;">
          Instagram
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="12" height="12" rx="2" stroke="#666666" stroke-width="1.5" fill="none"/><circle cx="7" cy="7" r="3" stroke="#666666" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="3.5" r="1" fill="#666666"/></svg>
        </a>
        <a href="https://linkedin.com" target="_blank" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 4px; font-family: 'Mona Sans', sans-serif; font-weight: 500; font-size: 12px; color: #0D0D0D;">
          LinkedIn
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="12" height="12" stroke="#666666" stroke-width="1.5" fill="none"/><path d="M4 6V10M4 4V4.01M6 10V6.5C6 5.67 6.67 5 7.5 5C8.33 5 9 5.67 9 6.5V10M9 4H10C11.1 4 12 4.9 12 6V10" stroke="#666666" stroke-width="1.5"/></svg>
        </a>
      </div>
    </div>
  </div>
</div>

{{-- JavaScript pour les interactions --}}
<script>
(function(){
  var searchInput = document.getElementById('hi3dSearchInput');
  var searchBtn = document.getElementById('hi3dSearchBtn');
  var searchOpenBtn = document.getElementById('hi3dSearchOpenBtn');
  var typeServices = document.getElementById('hi3dTypeServices');
  var typeArtists = document.getElementById('hi3dTypeArtists');
  var suggestions = document.getElementById('hi3dSuggestions');
  var suggestionsList = document.getElementById('hi3dSuggestionsList');
  var loginBtn = document.getElementById('hi3dLoginBtn');
  var mobileMenuBtn = document.getElementById('hi3dMobileMenuBtn');
  var mobileOverlay = document.getElementById('hi3dMobileOverlay');
  var mobilePanel = document.getElementById('hi3dMobilePanel');
  var mobileCloseBtn = document.getElementById('hi3dMobileCloseBtn');
  var mobileClose = document.getElementById('hi3dMobileClose');
  var mobileLoginBtn = document.getElementById('hi3dMobileLoginBtn');
  var mobileRegisterBtn = document.getElementById('hi3dMobileRegisterBtn');
  var mobileLogoutBtn = document.getElementById('hi3dMobileLogoutBtn');
  var scrollDownBtn = document.getElementById('hi3dScrollDownBtn');
  
  var currentType = 'Services';
  var frontendUrl = '{{ $frontendUrl ?? "" }}';
  var backendUrl = '{{ $backendUrl ?? "" }}';
  var apiBaseUrl = '{{ $apiBaseUrl ?? "" }}';

  console.log('[Nav] frontendUrl:', frontendUrl, 'backendUrl:', backendUrl);

  function normalizeUrl(url) {
    if (!url) return '';
    if (url.indexOf('//') === -1) {
      return window.location.protocol + '//' + window.location.host;
    }
    return url;
  }

  frontendUrl = frontendUrl || window.location.origin;
  backendUrl = backendUrl || window.location.origin;
  apiBaseUrl = apiBaseUrl || window.location.origin;

  // Check authentication from localStorage and update UI dynamically
  function checkAuthAndUpdateUI() {
    var token = localStorage.getItem('token');
    var userStr = localStorage.getItem('user');
    var isAuthenticated = !!(token && userStr);
    var authUser = null;
    
    if (isAuthenticated) {
      try {
        authUser = JSON.parse(userStr);
      } catch(e) {
        console.error('[Nav] Error parsing user from localStorage:', e);
        isAuthenticated = false;
      }
    }
    
    console.log('[Nav] Auth check:', { isAuthenticated, user: authUser });
    
    // Find the right part container in header
    var rightPart = document.querySelector('#hi3dHeader .ml-auto');
    if (!rightPart) return;
    
    if (isAuthenticated && authUser) {
      // Show connected user menu
      rightPart.innerHTML = '<div class="text-black flex items-center gap-[10px]" style="width: 182px; height: 48px; border-radius: 8px; padding: 6px; background: #F0F0F0;">' +
        '<button type="button" id="hi3dMobileSearchBtn" class="hidden lg:flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">' +
          '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<g clip-path="url(#hi3dClip0)"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.46962 11.16L0 15.6937L2.25 18L6.83437 13.527C7.99062 14.2465 9.32566 14.627 10.6875 14.625C14.7262 14.625 18 11.3434 18 7.3125C18 3.27375 14.7184 0 10.6875 0C6.64875 0 3.375 3.28163 3.375 7.3125C3.37297 8.67212 3.75219 10.0051 4.46962 11.16ZM15.7534 7.2585C15.7534 10.0485 13.4876 12.321 10.6909 12.321C7.90088 12.321 5.62838 10.0541 5.62838 7.2585C5.62838 4.4685 7.89525 2.196 10.6909 2.196C13.4809 2.196 15.7534 4.46175 15.7534 7.2585Z" fill="black"/>' +
            '<circle cx="2" cy="2" r="2" transform="matrix(-1 0 0 1 14 4)" fill="#0D0D0D"/>' +
            '</g><defs><clipPath id="hi3dClip0"><rect width="18" height="18" fill="white" transform="matrix(-1 0 0 1 18 0)"/></clipPath></defs>' +
          '</svg>' +
        '</button>' +
        '<a href="' + frontendUrl + '/favorite" class="flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">' +
          '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M12.9375 2.25H5.0625C4.76413 2.25 4.47798 2.36853 4.267 2.5795C4.05603 2.79048 3.9375 3.07663 3.9375 3.375V15.75C3.93755 15.8504 3.96446 15.9489 4.01545 16.0354C4.06643 16.1219 4.13963 16.1931 4.22744 16.2418C4.31525 16.2904 4.41448 16.3147 4.51483 16.312C4.61519 16.3094 4.713 16.2799 4.79812 16.2267L9 13.6005L13.2026 16.2267C13.2877 16.2797 13.3854 16.309 13.4857 16.3116C13.5859 16.3142 13.685 16.2899 13.7727 16.2412C13.8604 16.1926 13.9335 16.1214 13.9845 16.0351C14.0354 15.9487 14.0624 15.8503 14.0625 15.75V3.375C14.0625 3.07663 13.944 2.79048 13.733 2.5795C13.522 2.36853 13.2359 2.25 12.9375 2.25Z" fill="black"/>' +
          '</svg>' +
        '</a>' +
        '<a href="' + frontendUrl + '/messages" class="relative flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">' +
          '<svg width="18" height="18" viewBox="18 12 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M19.9603 24.0235H30.0398C30.6375 24.0235 31 23.727 31 23.2762C31 22.6587 30.3514 22.1028 29.8045 21.5532C29.3847 21.1271 29.2703 20.2501 29.2194 19.5399C29.1749 17.1684 28.5262 15.538 26.8347 14.9451C26.593 14.1361 25.938 13.5 24.9968 13.5C24.062 13.5 23.4006 14.1361 23.1654 14.9451C21.4738 15.538 20.8251 17.1684 20.7806 19.5399C20.7297 20.2501 20.6153 21.1271 20.1955 21.5532C19.6423 22.1028 19 22.6587 19 23.2762C19 23.727 19.3561 24.0235 19.9603 24.0235Z" fill="#0D0D0D"/>' +
          '</svg>' +
          '<span id="hi3dUnreadBadge" class="hidden absolute top-1 right-1 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full flex items-center justify-center" style="width: 16px; height: 16px;">0</span>' +
        '</a>' +
        '<a href="' + frontendUrl + '/dashboard/profile" class="flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">' +
          '<svg width="15" height="12" viewBox="0 0 15 12" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M13.5937 2H7.96873L6.09374 0H1.40625C0.629589 0 0 0.671559 0 1.5V10.5C0 11.3285 0.629589 12 1.40625 12H13.5937C14.3704 12 15 11.3285 15 10.5V3.50002C15 2.67157 14.3704 2 13.5937 2Z" fill="#0D0D0D"/>' +
          '</svg>' +
        '</a>' +
      '</div>';
      
      // Re-bind mobile search button event
      var mobileSearchBtn = document.getElementById('hi3dMobileSearchBtn');
      if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function() {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }
    } else {
      // Show login button
      rightPart.innerHTML = '<div class="flex items-center gap-3">' +
        '<button type="button" id="hi3dSearchOpenBtn" class="lg:hidden flex items-center justify-center" style="width: 50px; height: 40px; border-radius: 8px; background: #F0F0F0;">' +
          '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<g clip-path="url(#hi3dClipSearchBtn)"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.5304 11.16L18 15.6937L15.75 18L11.1656 13.527C10.0094 14.2465 8.67434 14.627 7.3125 14.625C3.27375 14.625 0 11.3434 0 7.3125C0 3.27375 3.28163 0 7.3125 0C11.3512 0 14.625 3.28163 14.625 7.3125C14.627 8.67212 14.2478 10.0051 13.5304 11.16ZM2.24662 7.2585C2.24662 10.0485 4.51238 12.321 7.30913 12.321C10.0991 12.321 12.3716 10.0541 12.3716 7.2585C12.3716 4.4685 10.1047 2.196 7.30913 2.196C4.51912 2.196 2.24662 4.46175 2.24662 7.2585Z" fill="black"/>' +
            '<circle cx="6" cy="6" r="2" fill="#0D0D0D"/>' +
            '</g><defs><clipPath id="hi3dClipSearchBtn"><rect width="18" height="18" fill="white"/></clipPath></defs>' +
          '</svg>' +
        '</button>' +
        '<button type="button" id="hi3dLoginBtn" class="transition-colors" style="width: 85px; height: 40px; padding: 12px 24px; gap: 5px; border-radius: 8px; background: #006EFF; font-family: \'Mona Sans\', \'Inter\', sans-serif; font-weight: 500; font-size: 14px; line-height: 16px; text-align: center; color: #FFFFFF; border: none; cursor: pointer;">Login</button>' +
      '</div>';
      
      // Re-bind events for newly created elements
      var newSearchOpenBtn = document.getElementById('hi3dSearchOpenBtn');
      var newLoginBtn = document.getElementById('hi3dLoginBtn');
      if (newSearchOpenBtn) {
        newSearchOpenBtn.addEventListener('click', function() {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }
      if (newLoginBtn) {
        newLoginBtn.addEventListener('click', handleLoginClick);
      }
    }
  }

  function openMobileMenu() {
    mobileOverlay.classList.remove('hidden');
    mobilePanel.style.transform = 'translateX(0)';
    document.body.style.overflow = 'hidden';
  }
  
  function closeMobileMenu() {
    mobileOverlay.classList.add('hidden');
    mobilePanel.style.transform = 'translateX(-100%)';
    document.body.style.overflow = '';
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
    
    var url = frontendUrl + '/search-global?search=' + encodeURIComponent(term);
    if (currentType) {
      url += '&type=' + encodeURIComponent(currentType);
    }
    console.log('[Nav] Redirecting to search:', url);
    window.location.href = url;
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

  function handleScrollDown() {
    window.scrollTo({
      top: document.body.scrollHeight,
      behavior: 'smooth'
    });
  }

  if (typeServices) typeServices.addEventListener('click', function() { setActiveType('Services'); });
  if (typeArtists) typeArtists.addEventListener('click', function() { setActiveType('Artists'); });
  if (searchBtn) searchBtn.addEventListener('click', performSearch);
  
  // Search button in header (loupe) - scroll to top on mobile
  if (searchOpenBtn) {
    searchOpenBtn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
  
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
  if (mobileMenuBtn) mobileMenuBtn.querySelector('button').addEventListener('click', openMobileMenu);
  if (mobileCloseBtn) mobileCloseBtn.addEventListener('click', closeMobileMenu);
  if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
  if (mobileLoginBtn) mobileLoginBtn.addEventListener('click', handleLoginClick);
  if (mobileRegisterBtn) mobileRegisterBtn.addEventListener('click', handleRegisterClick);
  if (mobileLogoutBtn) {
    mobileLogoutBtn.addEventListener('click', handleLogoutClick);
  }
  if (scrollDownBtn) {
    scrollDownBtn.addEventListener('click', handleScrollDown);
  }

  // Check auth on page load and update UI dynamically
  checkAuthAndUpdateUI();

  // Mobile Search Modal
  var searchOpenBtn = document.getElementById('hi3dSearchOpenBtn');
  var mobileSearchModal = document.getElementById('hi3dMobileSearchModal');
  var mobileSearchInput = document.getElementById('hi3dMobileSearchInput');
  var mobileSearchCloseBtn = document.getElementById('hi3dMobileSearchCloseBtn');
  var mobileSearchResults = document.getElementById('hi3dMobileSearchResults');
  var typeServicesMobile = document.getElementById('hi3dTypeServicesMobile');
  var typeArtistsMobile = document.getElementById('hi3dTypeArtistsMobile');
  var mobileSearchType = 'Services';

  function openMobileSearch() {
    mobileSearchModal.classList.remove('hidden');
    setTimeout(function() {
      if (mobileSearchInput) mobileSearchInput.focus();
    }, 100);
    document.body.style.overflow = 'hidden';
  }

  function closeMobileSearch() {
    mobileSearchModal.classList.add('hidden');
    document.body.style.overflow = '';
    if (mobileSearchInput) mobileSearchInput.value = '';
    if (mobileSearchResults) mobileSearchResults.innerHTML = '';
  }

  function setMobileSearchType(type) {
    mobileSearchType = type;
    if (typeServicesMobile && typeArtistsMobile) {
      if (type === 'Services') {
        typeServicesMobile.style.background = '#EDEDED';
        typeServicesMobile.style.color = '#333333';
        typeArtistsMobile.style.background = 'transparent';
        typeArtistsMobile.style.color = '#8E8E8E';
      } else {
        typeServicesMobile.style.background = 'transparent';
        typeServicesMobile.style.color = '#8E8E8E';
        typeArtistsMobile.style.background = '#EDEDED';
        typeArtistsMobile.style.color = '#333333';
      }
    }
  }

  function performMobileSearch() {
    var term = mobileSearchInput ? mobileSearchInput.value.trim() : '';
    if (!term) return;
    
    var url = frontendUrl + '/search-global?search=' + encodeURIComponent(term);
    if (mobileSearchType) {
      url += '&type=' + encodeURIComponent(mobileSearchType === 'Services' ? 'Services' : 'Artist 3D');
    }
    window.location.href = url;
  }

  function fetchMobileSearchSuggestions(query) {
    if (!query || query.length < 2) {
      if (mobileSearchResults) mobileSearchResults.innerHTML = '';
      return;
    }
    
    var searchType = mobileSearchType === 'Services' ? 'service_offers' : 'professional_profiles';
    var searchUrl = (backendUrl || window.location.origin) + '/api/search?q=' + encodeURIComponent(query) + '&types[]=' + searchType + '&per_page=10';
    
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
        mobileSearchResults.innerHTML = '<p class="p-4 text-gray-500">No results</p>';
      } else {
        var html = '';
        results.forEach(function(item) {
          var name = item.name || item.title || item.professional?.name || 'Untitled';
          var href = item.service_slug ? (frontendUrl || window.location.origin) + '/service/' + item.service_slug : (frontendUrl || window.location.origin) + '/professional/' + item.slug;
          html += '<a href="' + href + '" class="block p-3 hover:bg-gray-50 border-b border-gray-100">' + name + '</a>';
        });
        mobileSearchResults.innerHTML = html;
      }
    })
    .catch(function(err) {
      console.error('Mobile search error:', err);
      mobileSearchResults.innerHTML = '<p class="p-4 text-gray-500">Error loading suggestions</p>';
    });
  }

  if (searchOpenBtn) searchOpenBtn.addEventListener('click', openMobileSearch);
  if (mobileSearchCloseBtn) mobileSearchCloseBtn.addEventListener('click', closeMobileSearch);
  if (mobileSearchInput) {
    mobileSearchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        performMobileSearch();
      }
    });
    mobileSearchInput.addEventListener('input', function() {
      fetchMobileSearchSuggestions(this.value);
    });
  }
  if (typeServicesMobile) typeServicesMobile.addEventListener('click', function() { setMobileSearchType('Services'); });
  if (typeArtistsMobile) typeArtistsMobile.addEventListener('click', function() { setMobileSearchType('Artists'); });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeMobileMenu();
      closeMobileSearch();
    }
  });
})();
</script>