document.addEventListener('DOMContentLoaded', function(){
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.main-nav');
  if(toggle && nav){
    toggle.addEventListener('click', () => {
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', !expanded);
      if(!expanded){
        nav.style.display = 'flex';
      } else {
        nav.style.display = '';
      }
    });
  }

  // Simple focus ring for keyboard users (enhancement)
  document.body.addEventListener('keydown', function(e){
    if(e.key === 'Tab'){
      document.documentElement.classList.add('user-is-tabbing');
    }
  }, {once: true});
});
