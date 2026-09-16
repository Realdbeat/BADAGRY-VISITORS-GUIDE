/**
 * visitBadagry Replica - Interactive Logic
 * Handles Hero Carousel, Search/Filter, Lightbox, Modals, WhatsApp Chat, Scroll Progress
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ========================================================
     1. Hero Slider Carousel
  ======================================================== */
  const heroSlides = document.querySelectorAll('.hero-slide');
  const heroDots = document.querySelectorAll('.slider-dots .dot');
  const prevBtn = document.getElementById('sliderPrevBtn');
  const nextBtn = document.getElementById('sliderNextBtn');
  let currentSlide = 0;
  let slideInterval = null;
  const slideDuration = 5500;

  function showSlide(index) {
    if (index >= heroSlides.length) index = 0;
    if (index < 0) index = heroSlides.length - 1;

    heroSlides.forEach((slide, i) => {
      slide.classList.toggle('active', i === index);
    });

    heroDots.forEach((dot, i) => {
      dot.classList.toggle('active', i === index);
    });

    currentSlide = index;
  }

  function startSlideTimer() {
    stopSlideTimer();
    slideInterval = setInterval(() => {
      showSlide(currentSlide + 1);
    }, slideDuration);
  }

  function stopSlideTimer() {
    if (slideInterval) clearInterval(slideInterval);
  }

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', () => {
      showSlide(currentSlide - 1);
      startSlideTimer();
    });

    nextBtn.addEventListener('click', () => {
      showSlide(currentSlide + 1);
      startSlideTimer();
    });

    heroDots.forEach((dot) => {
      dot.addEventListener('click', (e) => {
        const dotIndex = parseInt(e.target.dataset.dot, 10);
        showSlide(dotIndex);
        startSlideTimer();
      });
    });

    const heroSection = document.getElementById('hero');
    if (heroSection) {
      heroSection.addEventListener('mouseenter', stopSlideTimer);
      heroSection.addEventListener('mouseleave', startSlideTimer);
    }

    startSlideTimer();
  }

  /* ========================================================
     2. Mobile Navigation Drawer
  ======================================================== */
  const mobileToggleBtn = document.getElementById('mobileToggleBtn');
  const mobileMenuDrawer = document.getElementById('mobileMenuDrawer');
  const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
  const drawerCloseBtn = document.getElementById('drawerCloseBtn');
  const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

  function openMobileMenu() {
    mobileMenuDrawer.classList.add('active');
    mobileMenuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeMobileMenu() {
    mobileMenuDrawer.classList.remove('active');
    mobileMenuOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', openMobileMenu);
  }
  if (drawerCloseBtn) {
    drawerCloseBtn.addEventListener('click', closeMobileMenu);
  }
  if (mobileMenuOverlay) {
    mobileMenuOverlay.addEventListener('click', closeMobileMenu);
  }

  mobileNavLinks.forEach(link => {
    link.addEventListener('click', closeMobileMenu);
  });

  /* ========================================================
     3. Search / Filter Form Logic
  ======================================================== */
  const tripSearchForm = document.getElementById('tripSearchForm');
  const durationSelect = document.getElementById('durationSelect');
  const budgetSelect = document.getElementById('budgetSelect');
  const destinationSelect = document.getElementById('destinationSelect');
  const tourCards = document.querySelectorAll('.tour-card');

  if (tripSearchForm) {
    tripSearchForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const durationVal = durationSelect.value;
      const budgetVal = budgetSelect.value;
      const destinationVal = destinationSelect.value;

      let matchedCount = 0;

      tourCards.forEach(card => {
        const cardDuration = card.dataset.duration;
        const cardPrice = parseInt(card.dataset.price, 10);
        const cardCorridor = card.dataset.corridor;

        let matchDuration = (durationVal === 'all' || durationVal === cardDuration);
        let matchDestination = (destinationVal === 'all' || cardCorridor.includes(destinationVal));
        
        let matchBudget = true;
        if (budgetVal === 'budget') {
          matchBudget = cardPrice <= 100;
        } else if (budgetVal === 'moderate') {
          matchBudget = cardPrice > 100 && cardPrice <= 350;
        } else if (budgetVal === 'premium') {
          matchBudget = cardPrice > 350;
        }

        if (matchDuration && matchBudget && matchDestination) {
          card.style.display = 'flex';
          card.style.animation = 'fadeIn 0.4s ease forwards';
          matchedCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Scroll smoothly to tours section
      const toursSec = document.getElementById('tours');
      if (toursSec) {
        toursSec.scrollIntoView({ behavior: 'smooth' });
      }

      // If no match found, alert user gently
      if (matchedCount === 0) {
        alert("No exact tour found for this specific filter combination. Showing all custom tour options!");
        tourCards.forEach(c => c.style.display = 'flex');
      }
    });
  }

  /* ========================================================
     4. Tour Category Filter Tabs
  ======================================================== */
  const tourTabs = document.querySelectorAll('.tour-tab-btn');

  tourTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tourTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filter = tab.dataset.filter;

      tourCards.forEach(card => {
        const cat = card.dataset.category;
        if (filter === 'all' || cat === filter) {
          card.style.display = 'flex';
          card.style.animation = 'fadeIn 0.4s ease forwards';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  /* ========================================================
     5. Tour Booking Modal
  ======================================================== */
  const bookingModalOverlay = document.getElementById('bookingModalOverlay');
  const modalCloseBtn = document.getElementById('modalCloseBtn');
  const formSelectedTour = document.getElementById('formSelectedTour');
  const bookingForm = document.getElementById('bookingForm');
  const bookingConfirmationMsg = document.getElementById('bookingConfirmationMsg');
  const openBookingBtns = document.querySelectorAll('.open-booking-btn');

  function openBookingModal(tourName) {
    if (formSelectedTour) {
      formSelectedTour.value = tourName || 'Badagry Heritage Tour';
    }
    if (bookingConfirmationMsg) {
      bookingConfirmationMsg.style.display = 'none';
    }
    bookingModalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeBookingModal() {
    bookingModalOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  const WHATSAPP_REDIRECT_URL = "http://wa.me/+2347056989224?text=from+google+map+or+site";

  openBookingBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const tourName = btn.dataset.tour;
      let url = WHATSAPP_REDIRECT_URL;
      if (tourName) {
        url = `http://wa.me/+2347056989224?text=${encodeURIComponent('from google map or site - Booking: ' + tourName)}`;
      }
      window.open(url, '_blank');
    });
  });

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', closeBookingModal);
  }

  if (bookingModalOverlay) {
    bookingModalOverlay.addEventListener('click', (e) => {
      if (e.target === bookingModalOverlay) {
        closeBookingModal();
      }
    });
  }

  if (bookingForm) {
    bookingForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('formFullName').value;
      const phone = document.getElementById('formPhone').value;
      const tour = formSelectedTour ? formSelectedTour.value : '';
      const notes = document.getElementById('formNotes') ? document.getElementById('formNotes').value : '';
      const msg = `from google map or site - Booking request for ${tour} by ${name} (${phone})${notes ? '. Note: ' + notes : ''}`;
      window.open(`http://wa.me/+2347056989224?text=${encodeURIComponent(msg)}`, '_blank');

      if (bookingConfirmationMsg) {
        bookingConfirmationMsg.style.display = 'flex';
        bookingConfirmationMsg.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  /* ========================================================
     6. Photo Gallery Lightbox
  ======================================================== */
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightboxModal = document.getElementById('lightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxClose = document.getElementById('lightboxClose');

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      const src = item.dataset.src;
      const caption = item.dataset.caption;

      if (lightboxImg && lightboxModal) {
        lightboxImg.src = src;
        if (lightboxCaption) lightboxCaption.textContent = caption;
        lightboxModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  function closeLightbox() {
    if (lightboxModal) {
      lightboxModal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  if (lightboxClose) {
    lightboxClose.addEventListener('click', closeLightbox);
  }

  if (lightboxModal) {
    lightboxModal.addEventListener('click', (e) => {
      if (e.target === lightboxModal) {
        closeLightbox();
      }
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeLightbox();
      closeBookingModal();
      closeMobileMenu();
    }
  });

  /* ========================================================
     7. WhatsApp Click-to-Chat Floating Widget
  ======================================================== */
  const waToggleBtn = document.getElementById('waToggleBtn');
  const waChatBox = document.getElementById('waChatBox');
  const waCloseBtn = document.getElementById('waCloseBtn');
  const waInput = document.getElementById('waInput');
  const waSendBtn = document.getElementById('waSendBtn');
  const waChatBody = document.querySelector('.wa-chat-body');

  if (waToggleBtn && waChatBox) {
    waToggleBtn.addEventListener('click', () => {
      waChatBox.classList.toggle('active');
    });

    if (waCloseBtn) {
      waCloseBtn.addEventListener('click', () => {
        waChatBox.classList.remove('active');
      });
    }

    function sendWaMessage() {
      const text = waInput.value.trim();
      const targetUrl = text 
        ? `http://wa.me/+2347056989224?text=${encodeURIComponent('from google map or site: ' + text)}`
        : "http://wa.me/+2347056989224?text=from+google+map+or+site";

      window.open(targetUrl, '_blank');
      waInput.value = '';
    }

    if (waSendBtn) {
      waSendBtn.addEventListener('click', sendWaMessage);
    }

    if (waInput) {
      waInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          sendWaMessage();
        }
      });
    }
  }

  function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /* ========================================================
     8. Circular Scroll Progress & Back-To-Top
  ======================================================== */
  const scrollTopBtn = document.getElementById('scrollTopBtn');
  const circle = document.querySelector('.progress-ring__circle');

  if (circle && scrollTopBtn) {
    const radius = circle.r.baseVal.value;
    const circumference = 2 * Math.PI * radius;
    circle.style.strokeDasharray = `${circumference} ${circumference}`;
    circle.style.strokeDashoffset = `${circumference}`;

    function setProgress(percent) {
      const offset = circumference - (percent / 100) * circumference;
      circle.style.strokeDashoffset = offset;
    }

    window.addEventListener('scroll', () => {
      const scrollTop = window.scrollY || document.documentElement.scrollTop;
      const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrollPercent = (scrollTop / docHeight) * 100;

      setProgress(scrollPercent);

      if (scrollTop > 300) {
        scrollTopBtn.classList.add('visible');
      } else {
        scrollTopBtn.classList.remove('visible');
      }
    });

    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  /* ========================================================
     9. Newsletter Subscription
  ======================================================== */
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = newsletterForm.querySelector('.newsletter-input');
      if (emailInput && emailInput.value) {
        alert(`Thank you! ${emailInput.value} has been subscribed to Badagry Visitors Guide travel discounts & guides.`);
        newsletterForm.reset();
      }
    });
  }

});
