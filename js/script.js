// Global alert helper
function alert(type, msg, position = 'body') {
  let bs_class = (type == 'success') ? 'alert-success' : (type == 'danger') ? 'alert-danger' : 'alert-info';
  let element = document.createElement('div');
  element.innerHTML = `
      <div class="alert ${bs_class} alert-dismissible fade show custom-alert" role="alert">
          <strong class="me-3">${msg}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;

  if (position == 'body') {
      document.body.append(element);
      element.classList.add('custom-alert-position');
  } else if (position == 'dashboard') {
      const holder = document.getElementById('dashboard-alert');
      if (holder) {
        holder.appendChild(element);
        element.classList.add('custom-alert-dashboard');
      } else {
        document.body.append(element);
      }
  }
  setTimeout(() => {
      const alertEl = element.querySelector('.alert');
      if (!alertEl) return;
      try {
        let bsAlert = new bootstrap.Alert(alertEl);
        bsAlert.close();
      } catch (e) {
        // Bootstrap may not be ready; remove manually
        element.remove();
      }
  }, 3000);
}

// Password show/hide toggle
(function() {
  function setupPasswordToggles() {
      document.querySelectorAll('.toggle-password').forEach(btn => {
          btn.addEventListener('click', () => {
              const input = document.querySelector(btn.getAttribute('data-target'));
              if (!input) return;
              const isPassword = input.getAttribute('type') === 'password';
              input.setAttribute('type', isPassword ? 'text' : 'password');
              const icon = btn.querySelector('i');
              if (icon) {
                  icon.classList.toggle('bi-eye');
                  icon.classList.toggle('bi-eye-slash');
              }
          });
      });
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setupPasswordToggles);
  } else {
      setupPasswordToggles();
  }
})();

// Login form handler
(function(){
  let login_form = document.getElementById('login-form');
  if (!login_form) return;
  login_form.addEventListener('submit', function(e) {
      e.preventDefault();
      let data = new FormData();
      data.append('email', login_form.elements['email'].value);
      data.append('pass', login_form.elements['pass'].value);
      data.append('login', '');

      let xhr = new XMLHttpRequest();
      xhr.open('POST', 'ajax/login_register.php', true);
      xhr.onload = function() {
          if (this.responseText == 'inv_email') {
              alert('danger', 'Invalid Email!')
          } else if (this.responseText == 'not_verified') {
              alert('danger', 'Email not verified!')
          } else if (this.responseText == 'inactive') {
              alert('danger', 'Account has been suspended! Please contact Admin!')
          } else if (this.responseText == 'invalid_pass') {
              alert('danger', 'Incorrect Password!')
          } else {
              let modal = document.getElementById('loginModal');
              if (modal) {
                let modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                modalInstance.hide();
              }
              window.location.reload();
          }
      }
      xhr.send(data);
  });
})();

// Register form handler
(function(){
  let register_form = document.getElementById('register-form');
  if (!register_form) return;

  register_form.addEventListener('submit', function(e) {
      e.preventDefault();

      let data = new FormData();
      data.append('name', register_form.elements['name'].value);
      data.append('email', register_form.elements['email'].value);
      data.append('phonenum', register_form.elements['phonenum'].value);
      data.append('address', register_form.elements['address'].value);
      data.append('pincode', register_form.elements['pincode'].value);
      data.append('dob', register_form.elements['dob'].value);
      data.append('pass', register_form.elements['pass'].value);
      data.append('cpass', register_form.elements['cpass'].value);
      data.append('register', '');

      if (register_form.elements['profile'] && register_form.elements['profile'].files.length > 0) {
          data.append('profile', register_form.elements['profile'].files[0]);
      }

      let xhr = new XMLHttpRequest();
      xhr.open('POST', 'ajax/login_register.php', true);

      xhr.onload = function() {
          if (this.responseText == 'pass_mismatch') {
              alert('danger', 'Password mismatch! Please retype');
          } else if (this.responseText == 'email_already') {
              alert('danger', 'Email already registered!');
          } else if (this.responseText == 'phone_already') {
              alert('danger', 'Phone number already used!');
          } else if (this.responseText == 'file_upload_fail') {
              alert('danger', 'Image upload failed!');
          } else if (this.responseText == 'inv_img') {
              alert('danger', 'Image must be jpg, png, or webp format');
          } else if (this.responseText == 'inv_size') {
              alert('danger', 'Image size must be 2MB or less');
          } else if (this.responseText == 'mail_failed') {
              alert('danger', 'Cannot send confirmation email! Server Down!');
          } else if (this.responseText == 'ins_failed') {
              alert('danger', 'Registration failed! Server Down!');
          } else {
              let modal = document.getElementById('registerModal');
              if (modal) {
                let modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                modalInstance.hide();
              }
              alert('success', 'Registration successful! Check your email to verify your account.');
              register_form.reset();
          }
      }
      xhr.send(data);
  });
})();

// Navbar scroll effect (applies site-wide where .transparent-navbar exists)
(function(){
  window.addEventListener('scroll', function(){
    const navbar = document.querySelector('.transparent-navbar');
    if (!navbar) return;
    if (window.scrollY > 50) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
  });
})();

// Swiper initializations (guarded by presence of elements)
(function(){
  if (typeof Swiper === 'undefined') return;

  const swiperEl = document.querySelector('.mySwiper');
  if (swiperEl) {
    new Swiper('.mySwiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      pagination: { el: '.swiper-pagination', clickable: true },
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
    });
  }

  const testEl = document.querySelector('.swiper-testimonials');
  if (testEl) {
    new Swiper('.swiper-testimonials', {
      effect: 'coverflow',
      grabCursor: true,
      centeredSlides: true,
      slidesPerView: 'auto',
      coverflowEffect: { rotate: 50, stretch: 0, depth: 100, modifier: 1, slideShadows: false },
      pagination: { el: '.swiper-pagination' },
      breakpoints: {
        320: { slidesPerView: 1 },
        640: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3 },
      }
    });
  }
})();

// DOMContentLoaded interactions for home page enhancements
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
      setTimeout(() => loadingOverlay.classList.add('hide'), 1000);
    }

    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
      window.addEventListener('scroll', function(){
        if (window.pageYOffset > 300) scrollToTopBtn.classList.add('show');
        else scrollToTopBtn.classList.remove('show');
      });
      scrollToTopBtn.addEventListener('click', function(){
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    // Fade-in on scroll
    const elementsToAnimate = document.querySelectorAll('.room-card-enhanced, .facility-card-enhanced, .testimonial-card-enhanced, .contact-card-enhanced');
    if (elementsToAnimate.length) {
      const observer = new IntersectionObserver(function(entries){
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
      }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
      elementsToAnimate.forEach(el => { el.classList.add('fade-in'); observer.observe(el); });
    }

    // Enhanced form interactions
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
      input.addEventListener('focus', function(){ this.parentElement?.classList.add('focused'); });
      input.addEventListener('blur', function(){ if (!this.value) this.parentElement?.classList.remove('focused'); });
    });

    // Smooth anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e){
        // allow hash links that are real anchors only
        const href = this.getAttribute('href');
        if (!href || href === '#') return;
        const target = document.querySelector(href);
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
      });
    });
  });
})();

// checkLogin utilities
(function(){
  // Derive login state from a data attribute on body that server sets
  const isLoggedIn = (function(){
    const body = document.body;
    const attr = body ? body.getAttribute('data-logged-in') : null;
    return attr === '1' || attr === 'true';
  })();

  window.checkLogin = function(roomId){
    if (isLoggedIn) {
      // If called on rooms page want to book, go to booking.php else default to rooms
      if (roomId != null) {
        window.location.href = 'booking.php?room_id=' + encodeURIComponent(roomId);
      } else {
        window.location.href = 'rooms.php';
      }
      return true;
    } else {
      // Prefer opening login modal if exists; else redirect to index
      const modalEl = document.getElementById('loginModal');
      if (modalEl && window.bootstrap?.Modal) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      } else {
        window.location.href = 'index.php';
      }
      return false;
    }
  }
})();

// Rooms page rotating hero background
(function(){
  const heroSection = document.querySelector('.hero-section');
  const backgroundImages = [
    'images/rooms/IMG_12341.jpg',
    'images/rooms/IMG_19430.jpg',
    'images/rooms/IMG_25323.jpg',
    'images/rooms/IMG_29030.jpg',
    'images/rooms/IMG_41616.jpg'
  ];
  if (heroSection) {
    let idx = 0;
    setInterval(function(){
      idx = (idx + 1) % backgroundImages.length;
      const newImage = backgroundImages[idx];
      heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('${newImage}')`;
    }, 10000);
  }
})();

// Rooms slideshow (home page) initialized via JSON data in #roomsData
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const dataEl = document.getElementById('roomsData');
    let rooms = [];
    try { rooms = dataEl ? JSON.parse(dataEl.textContent) : []; } catch(e) { rooms = []; }
    if (!rooms || rooms.length === 0) return;

    const roomImage = document.getElementById('roomImage');
    const roomTitle = document.getElementById('roomTitle');
    const roomPrice = document.getElementById('roomPrice');
    const roomFeatures = document.getElementById('roomFeatures');
    const roomGuests = document.getElementById('roomGuests');
    const roomDescription = document.getElementById('roomDescription');
    const bookNowBtn = document.getElementById('bookNowBtn');
    const moreDetailsBtn = document.getElementById('moreDetailsBtn');
    const indicatorsContainer = document.getElementById('roomIndicators');
    const prevBtn = document.getElementById('prevSlide');
    const nextBtn = document.getElementById('nextSlide');
    const slideshow = document.getElementById('roomSlideshow');

    if (!roomImage || !roomTitle || !roomPrice || !roomFeatures || !roomGuests || !roomDescription || !bookNowBtn || !moreDetailsBtn || !indicatorsContainer || !prevBtn || !nextBtn || !slideshow) {
      return;
    }

    let currentSlide = 0;
    let slideInterval;

    function createIndicators() {
      indicatorsContainer.innerHTML = '';
      rooms.forEach((_, index) => {
        const indicator = document.createElement('div');
        indicator.className = 'room-indicator' + (index === 0 ? ' active' : '');
        indicator.addEventListener('click', function(){ goToSlide(index); });
        indicatorsContainer.appendChild(indicator);
      });
    }

    function updateSlide(index) {
      const room = rooms[index];
      if (!room) return;
      roomImage.src = room.image;
      roomImage.alt = room.name || '';
      roomTitle.textContent = room.name || '';
      roomPrice.textContent = room.price != null ? `₱${room.price}` : '';
      roomFeatures.innerHTML = `
        <h6 class="mb-2 fw-semibold" style="color: #495057;">Features</h6>
        <div class="d-flex flex-wrap">${room.features || ''}</div>
      `;
      roomGuests.innerHTML = `
        <h6 class="mb-2 fw-semibold" style="color: #495057;">Guests</h6>
        <div class="d-flex gap-2">
          <span class="badge">
            <i class="bi bi-person-fill me-1"></i>${room.adult ?? ''} Adults
          </span>
          <span class="badge">
            <i class="bi bi-people-fill me-1"></i>${room.children ?? ''} Children
          </span>
        </div>
      `;
      roomDescription.textContent = room.description || 'Experience luxury and comfort in this beautifully designed room with modern amenities and stunning views.';
      // On "Check Availability", go to rooms.php as requested
      bookNowBtn.onclick = function(){ window.location.href = 'rooms.php'; };
      moreDetailsBtn.href = 'room_details.php?id=' + encodeURIComponent(room.id);

      document.querySelectorAll('.room-indicator').forEach((indicator, i) => {
        indicator.classList.toggle('active', i === index);
      });
    }

    function goToSlide(index){ currentSlide = index; updateSlide(currentSlide); resetInterval(); }
    function nextSlide(){ currentSlide = (currentSlide + 1) % rooms.length; updateSlide(currentSlide); }
    function prevSlide(){ currentSlide = (currentSlide - 1 + rooms.length) % rooms.length; updateSlide(currentSlide); }

    function startAutoSlide(){ slideInterval = setInterval(nextSlide, 5000); }
    function resetInterval(){ clearInterval(slideInterval); startAutoSlide(); }

    nextBtn.addEventListener('click', function(){ nextSlide(); resetInterval(); });
    prevBtn.addEventListener('click', function(){ prevSlide(); resetInterval(); });
    slideshow.addEventListener('mouseenter', function(){ clearInterval(slideInterval); });
    slideshow.addEventListener('mouseleave', startAutoSlide);

    if (rooms.length > 0) {
      createIndicators();
      updateSlide(0);
      startAutoSlide();
    }
  });
})();

// Facilities slider (home page) initialized via JSON data in #facilitiesData and #facilitiesImgPath
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const dataEl = document.getElementById('facilitiesData');
    const pathEl = document.getElementById('facilitiesImgPath');

    let facilitiesData = [];
    let facilitiesImgPath = '';
    try { facilitiesData = dataEl ? JSON.parse(dataEl.textContent) : []; } catch(e) { facilitiesData = []; }
    try { facilitiesImgPath = pathEl ? JSON.parse(pathEl.textContent) : ''; } catch(e) { facilitiesImgPath = ''; }

    if (!facilitiesData || facilitiesData.length === 0) return;

    const dotsContainer = document.getElementById('sliderDots');
    const image = document.getElementById('facilityImage');
    const title = document.getElementById('facilityTitle');
    const description = document.getElementById('facilityDescription');
    const card = document.getElementById('facilitySlider');

    if (!dotsContainer || !image || !title || !description || !card) return;

    let currentFacilityIndex = 0;
    let sliderInterval;

    function createDots(){
      dotsContainer.innerHTML = '';
      facilitiesData.forEach(function(_, index){
        const dot = document.createElement('span');
        dot.className = 'slider-dot';
        dot.addEventListener('click', function(){ goToFacility(index); });
        dotsContainer.appendChild(dot);
      });
    }

    function showFacility(index){
      const facility = facilitiesData[index];
      if (!facility) return;
      if (card) card.style.opacity = '0.7';
      setTimeout(function(){
        const imageSrc = (facilitiesImgPath || '') + (facility.icon || '');
        image.src = imageSrc;
        image.alt = facility.name || '';
        title.textContent = facility.name || '';
        description.textContent = facility.description || '';
        const dots = dotsContainer.querySelectorAll('.slider-dot');
        dots.forEach(function(dot, i){ dot.classList.toggle('active', i === index); });
        if (card) card.style.opacity = '1';
      }, 200);
      currentFacilityIndex = index;
    }

    function nextFacility(){ const nextIndex = (currentFacilityIndex + 1) % facilitiesData.length; showFacility(nextIndex); }
    function startAutoRotation(){ sliderInterval = setInterval(nextFacility, 4000); }
    function resetAutoRotation(){ clearInterval(sliderInterval); startAutoRotation(); }
    function goToFacility(i){ showFacility(i); resetAutoRotation(); }

    card.addEventListener('mouseenter', function(){ clearInterval(sliderInterval); });
    card.addEventListener('mouseleave', startAutoRotation);

    createDots();
    showFacility(0);
    startAutoRotation();
  });
})();
// Graceful fallback for remote testimonial avatars
(function () {
  function replaceBrokenImg(img) {
    if (!img || img.dataset.fallbackApplied === '1') return;
    img.onerror = null;
    img.src = 'images/avatar_fallback.svg';
    img.alt = 'User avatar';
    img.dataset.fallbackApplied = '1';
  }

  function applyFallbacks() {
    document.querySelectorAll('.testimonial-card img.testimonial-avatar').forEach(function (img) {
      if (!img) return;
      // apply onerror each run
      img.addEventListener('error', function () { replaceBrokenImg(img); }, { once: true });
      // Also proactively swap if blocked by CSP or offline cache missing
      if (!img.complete || img.naturalWidth === 0) {
        // Give the network a short window; then verify
        setTimeout(function () {
          if (!img.complete || img.naturalWidth === 0) replaceBrokenImg(img);
        }, 1500);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyFallbacks);
  } else {
    applyFallbacks();
  }
})();