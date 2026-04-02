<header class="sticky top-0 z-40 w-full backdrop-blur-lg bg-white/80 border-b border-gray-200">
  <div class="container mx-auto px-4 md:px-10">
    <div class="flex items-center justify-between h-16">
      <a href="/" class="flex-shrink-0">
        <img src="/img/logo.svg" alt="Hi3D Logo" class="h-6 w-auto" />
      </a>

      <nav class="hidden md:flex items-center gap-4">
        <a href="/login" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">Login</a>
        <a href="/register" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">Sign up</a>
        <a href="/subscription" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 rounded-full transition-opacity">Become a Pro</a>
      </nav>

      <button class="md:hidden p-2 -mr-2 text-gray-600 hover:text-gray-900" id="hi3dHamburger" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>
</header>

<div class="fixed bottom-2 left-4 md:left-10 z-40">
  <button class="md:hidden p-3 bg-white rounded-full shadow-lg text-gray-600 hover:text-gray-900" id="hi3dMobileMenuToggle" aria-label="Menu">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</div>

<div class="fixed bottom-2 right-4 md:right-10 z-40">
  <a href="/subscription" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 rounded-full shadow-lg transition-opacity">
    Become a Pro
  </a>
</div>

<div class="fixed inset-0 z-50 hidden" id="hi3dMobileMenuOverlay">
  <div class="absolute inset-0 bg-black/50" id="hi3dMobileMenuClose"></div>
  <div class="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-xl p-6 transform transition-transform" id="hi3dMobileMenuPanel">
    <div class="flex justify-end mb-4">
      <button class="p-2 text-gray-500 hover:text-gray-700" id="hi3dMobileMenuCloseBtn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="flex flex-col gap-4">
      <a href="/login" class="w-full py-3 text-center font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Login</a>
      <a href="/register" class="w-full py-3 text-center font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Sign up</a>
    </div>
  </div>
</div>

<script>
(function(){
  var toggle=document.getElementById('hi3dMobileMenuToggle');
  var overlay=document.getElementById('hi3dMobileMenuOverlay');
  var closeBtn=document.getElementById('hi3dMobileMenuCloseBtn');
  var closeArea=document.getElementById('hi3dMobileMenuClose');
  var panel=document.getElementById('hi3dMobileMenuPanel');

  function openMenu(){overlay.classList.remove('hidden');panel.style.transform='translateX(0)'}
  function closeMenu(){overlay.classList.add('hidden');panel.style.transform='translateX(-100%)'}

  if(toggle)toggle.addEventListener('click',openMenu);
  if(closeBtn)closeBtn.addEventListener('click',closeMenu);
  if(closeArea)closeArea.addEventListener('click',closeMenu);
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeMenu()});
})();
</script>
