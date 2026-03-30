<style>
.hi3d-header *{box-sizing:border-box;margin:0;padding:0}
.hi3d-header{font-family:'Mona Sans','Inter',system-ui,sans-serif;width:100%}
.hi3d-header-inner{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;min-height:60px;padding:20px 10px;padding-left:40px;padding-right:40px}
@media(max-width:768px){.hi3d-header-inner{padding-left:10px;padding-right:10px}}
.hi3d-logo{cursor:pointer;flex-shrink:0}
.hi3d-logo img{width:32px;height:24px;display:block}
.hi3d-nav{display:flex;align-items:center;gap:16px;margin-left:auto}
.hi3d-btn-login{display:inline-flex;align-items:center;justify-content:center;width:85px;height:40px;padding:12px 24px;border-radius:8px;background:#006EFF;font-size:14px;font-weight:500;color:#fff;border:none;cursor:pointer;text-decoration:none;transition:background .2s}
.hi3d-btn-login:hover{background:#0055CC}
.hi3d-btn-signup{display:inline-flex;align-items:center;justify-content:center;padding:10px 20px;border-radius:24px;border:1px solid #fff;font-size:14px;font-weight:500;color:#fff;background:transparent;cursor:pointer;text-decoration:none;transition:all .2s}
.hi3d-btn-signup:hover{background:#fff;color:#000}
.hi3d-hamburger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:8px}
.hi3d-hamburger span{display:block;width:24px;height:2px;background:#000;transition:all .3s}
.hi3d-mobile-menu{position:fixed;top:0;right:-100%;width:80%;max-width:320px;height:100vh;background:#fff;z-index:1000;transition:right .3s;padding:24px;display:flex;flex-direction:column;gap:16px}
.hi3d-mobile-menu.open{right:0}
.hi3d-mobile-overlay{position:fixed;top:0;left:0;width:100%;height:100vh;background:rgba(0,0,0,.5);z-index:999;display:none}
.hi3d-mobile-overlay.open{display:block}
.hi3d-mobile-close{background:none;border:none;font-size:24px;cursor:pointer;align-self:flex-end}
.hi3d-pro-badge{display:inline-flex;align-items:center;padding:6px 12px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:16px;font-size:12px;font-weight:600;color:#fff;text-decoration:none;transition:transform .2s}
.hi3d-pro-badge:hover{transform:scale(1.05)}
@media(max-width:768px){.hi3d-hamburger{display:flex}.hi3d-nav{display:none}}
</style>

<div class="hi3d-header">
  <div class="hi3d-header-inner">
    <a href="/" class="hi3d-logo">
      <img src="/img/logo.svg" alt="Hi3D Logo" />
    </a>

    <nav class="hi3d-nav">
      <a href="/login" class="hi3d-btn-login">Login</a>
      <a href="/register" class="hi3d-btn-signup">Sign up</a>
      <a href="/subscription" class="hi3d-pro-badge">Become a Pro</a>
    </nav>

    <button class="hi3d-hamburger" id="hi3dHamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <div class="hi3d-mobile-overlay" id="hi3dOverlay"></div>
  <div class="hi3d-mobile-menu" id="hi3dMobileMenu">
    <button class="hi3d-mobile-close" id="hi3dClose">&times;</button>
    <a href="/login" class="hi3d-btn-login" style="width:100%;text-align:center;">Login</a>
    <a href="/register" class="hi3d-btn-signup" style="width:100%;text-align:center;border-color:#000;color:#000;">Sign up</a>
    <a href="/subscription" class="hi3d-pro-badge" style="justify-content:center;">Become a Pro</a>
  </div>
</div>

<script>
(function(){
  var hamburger=document.getElementById('hi3dHamburger');
  var mobileMenu=document.getElementById('hi3dMobileMenu');
  var overlay=document.getElementById('hi3dOverlay');
  var closeBtn=document.getElementById('hi3dClose');

  function openMenu(){mobileMenu.classList.add('open');overlay.classList.add('open');document.body.style.overflow='hidden'}
  function closeMenu(){mobileMenu.classList.remove('open');overlay.classList.remove('open');document.body.style.overflow=''}

  if(hamburger)hamburger.addEventListener('click',openMenu);
  if(closeBtn)closeBtn.addEventListener('click',closeMenu);
  if(overlay)overlay.addEventListener('click',closeMenu);
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeMenu()});
})();
</script>
