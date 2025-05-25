/*=============== MENU SHOW AND HIDE ===============*/
const navMenu = document.getElementById('nav-menu'),
      navToggle = document.getElementById('nav-toggle'),
      navClose = document.getElementById('nav-close');

if(navToggle){
  navToggle.addEventListener('click', () => {
    navMenu.classList.add('show-menu');
  });
}

if(navClose){
  navClose.addEventListener('click', () => {
    navMenu.classList.remove('show-menu');
  });
}

/*=============== REMOVE MENU ON LINK CLICK ===============*/
const navLinks = document.querySelectorAll('.nav__link');

navLinks.forEach(n => n.addEventListener('click', () => {
  navMenu.classList.remove('show-menu');
}));

/*=============== CHANGE BACKGROUND HEADER ON SCROLL ===============*/
function scrollHeader(){
  const header = document.getElementById('header');
  if(this.scrollY >= 80) header.classList.add('scroll-header');
  else header.classList.remove('scroll-header');
}
window.addEventListener('scroll', scrollHeader);

/*=============== SHOW SCROLL UP BUTTON ===============*/
function scrollUp(){
  const scrollUp = document.getElementById('scroll-up');
  if(this.scrollY >= 400) scrollUp.classList.add('show-scroll');
  else scrollUp.classList.remove('show-scroll');
}
window.addEventListener('scroll', scrollUp);

/*=============== DARK/LIGHT THEME TOGGLE ===============*/
const themeButton = document.getElementById('theme-button');
const darkTheme = 'dark-theme';
const iconTheme = 'bx-sun';

// Previously selected theme (if user selected)
const selectedTheme = localStorage.getItem('selected-theme');
const selectedIcon = localStorage.getItem('selected-icon');

// Get current theme
const getCurrentTheme = () => document.body.classList.contains(darkTheme) ? 'dark' : 'light';
const getCurrentIcon = () => themeButton.classList.contains(iconTheme) ? 'bx-moon' : 'bx-sun';

// Apply previously selected theme and icon
if(selectedTheme){
  if(selectedTheme === 'dark') document.body.classList.add(darkTheme);
  else document.body.classList.remove(darkTheme);

  if(selectedIcon === 'bx-moon') themeButton.classList.add(iconTheme);
  else themeButton.classList.remove(iconTheme);
}

// Toggle theme & save to localStorage
themeButton.addEventListener('click', () => {
  document.body.classList.toggle(darkTheme);
  themeButton.classList.toggle(iconTheme);
  
  localStorage.setItem('selected-theme', getCurrentTheme());
  localStorage.setItem('selected-icon', getCurrentIcon());
});

/*=============== SWIPER SLIDER ===============*/
const swiper = new Swiper('.new-swiper', {
  spaceBetween: 24,
  loop: true,
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: 'auto',
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  breakpoints: {
    576: { slidesPerView: 2 },
    768: { slidesPerView: 3 },
    1024: { slidesPerView: 4 },
  }
});
