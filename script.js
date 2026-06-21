/* ===== Prepare variables for Typewriter Effect ===== */
// Main typewriter effect variables
let typewriterElement;
let wordIndex = 0;
let charIndex = 0;
let isDeleting = false;
let isPaused = false;
let words = ['LEARNER', 'TECH ENTHUSIAST', 'STUDENT']; // Default words

// Logo welcome text effect variables
let logoTextElement;
let logoCharIndex = 0;
let isLogoDeleting = false;
let isLogoAnimationComplete = false;
let welcomeText = 'WELCOME'; // Default welcome text
let wasInHomeSection = true; // Track previous state to avoid unnecessary restarts

// Function definitions
function typeWriter() {
  if (isPaused || !typewriterElement) return;

  const currentWord = words[wordIndex];
  
  if (!isDeleting) {
    // Typing
    const displayText = currentWord.substring(0, charIndex + 1);
    typewriterElement.innerHTML = displayText + '<span class="cursor"></span>';
    charIndex++;
    
    if (charIndex === currentWord.length) {
      // Word complete, pause then start deleting
      isPaused = true;
      setTimeout(() => {
        isPaused = false;
        isDeleting = true;
        typeWriter();
      }, 1500); // 1.5 second pause
      return;
    }
  } else {
    // Deleting
    const displayText = currentWord.substring(0, charIndex - 1);
    typewriterElement.innerHTML = displayText + '<span class="cursor"></span>';
    charIndex--;
    
    if (charIndex === 0) {
      // Word deleted, move to next word
      isDeleting = false;
      wordIndex = (wordIndex + 1) % words.length;
      isPaused = true;
      setTimeout(() => {
        isPaused = false;
        typeWriter();
      }, 500); // Brief pause before next word
      return;
    }
  }

  // Continue typing/deleting
  const speed = isDeleting ? 100 : 150; // Deleting is faster
  setTimeout(typeWriter, speed);
}

// Logo typewriter effect function
function logoTypeWriter() {
  if (!logoTextElement || isLogoAnimationComplete) return;
  
  if (!isLogoDeleting) {
    // Typing welcome
    logoTextElement.textContent = welcomeText.substring(0, logoCharIndex + 1);
    logoCharIndex++;
    
    if (logoCharIndex === welcomeText.length) {
      // Word complete, pause then start deleting
      setTimeout(() => {
        if (!isLogoAnimationComplete) { // Check if animation should continue
          isLogoDeleting = true;
          logoTypeWriter();
        }
      }, 1500); // 1.5 second pause
      return;
    }
  } else {
    // Deleting welcome
    logoTextElement.textContent = welcomeText.substring(0, logoCharIndex - 1);
    logoCharIndex--;
    
    if (logoCharIndex === 0) {
      // Complete animation cycle - stop here instead of looping
      isLogoAnimationComplete = true;
      logoTextElement.textContent = ''; // Clear text after animation completes
      return;
    }
  }

  // Continue typing/deleting
  const speed = isLogoDeleting ? 80 : 120; // Deleting is faster
  setTimeout(() => {
    if (!isLogoAnimationComplete) { // Check if animation should continue
      logoTypeWriter();
    }
  }, speed);
}

// Control logo visibility on scroll - This is a global function that will be attached in DOMContentLoaded
function handleLogoVisibility() {
  if (!logoTextElement) return;
  
  const homeSection = document.getElementById('home');
  const isInHomeSection = homeSection && window.scrollY < homeSection.offsetHeight;
  
  if (isInHomeSection) {
    // In home section - show logo
    logoTextElement.style.opacity = '1';
    
    // Only restart animation if we're returning from another section
    if (!wasInHomeSection && isLogoAnimationComplete) {
      // Restart animation only when returning from outside home section
      isLogoAnimationComplete = false;
      logoCharIndex = 0;
      isLogoDeleting = false;
      logoTypeWriter();
    }
    wasInHomeSection = true;
  } else {
    // Outside home section - hide logo and stop animation
    logoTextElement.style.opacity = '0';
    isLogoAnimationComplete = true; // Stop the animation
    logoTextElement.textContent = ''; // Clear text immediately when scrolling away
    wasInHomeSection = false;
  }
}

/* ===== Sticky header + active links ===== */
const header = document.getElementById('header');
const nav = document.getElementById('nav');
const navToggle = document.getElementById('navToggle');
const links = [...document.querySelectorAll('.nav-link')];

function onScroll() {
  header.classList.toggle('scrolled', window.scrollY > 10);

  const fromTop = window.scrollY + 90;
  links.forEach(link => {
    const id = link.getAttribute('href');
    if (!id || !id.startsWith('#')) return;
    const sec = document.querySelector(id);
    if (!sec) return;
    const top = sec.offsetTop;
    const bottom = top + sec.offsetHeight;
    if (fromTop >= top && fromTop < bottom) {
      links.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    }
  });
}
window.addEventListener('scroll', onScroll);
onScroll();

/* ===== Mobile menu ===== */
navToggle.addEventListener('click', () => {
  const open = document.body.classList.toggle('nav-open');
  navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
});
nav.addEventListener('click', e => {
  if (e.target.matches('.nav-link')) {
    document.body.classList.remove('nav-open');
    navToggle.setAttribute('aria-expanded', 'false');
  }
});

/* ===== Skills bars (animate when visible) ===== */
const skillBars = document.querySelectorAll('.bar');
const barObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const el = entry.target;
      const val = el.dataset.value || '0';
      el.style.setProperty('--val', val + '%');
      barObserver.unobserve(el);
    }
  });
}, { threshold: 0.4 });
skillBars.forEach(b => barObserver.observe(b));

/* ===== Counters (stats section) ===== */
const counters = document.querySelectorAll('.stat .num');
const counterObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el = entry.target;
    const to = +el.dataset.count;
    const dur = 1200;
    const start = performance.now();
    const step = (t) => {
      const p = Math.min(1, (t - start) / dur);
      el.textContent = Math.floor(to * (0.2 + 0.8 * p));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
    counterObserver.unobserve(el);
  });
}, { threshold: 0.6 });
counters.forEach(c => counterObserver.observe(c));


/* ===== Project filters ===== */
const filterWrap = document.getElementById('filters');
//const tiles = [...document.querySelectorAll('.project-grid .tile')];

// Only add the click handler if the element exists
if (filterWrap) {
  const tiles = [...document.querySelectorAll('.project-grid .tile')];
  filterWrap.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    filterWrap.querySelectorAll('button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const f = btn.dataset.filter;
    tiles.forEach(t => {
      // defensively handle missing data-cat
      const cat = t.dataset.cat || '';
      t.style.display = (f === 'all' || cat.includes(f)) ? '' : 'none';
    });
  });
}

/* ===== Certificate filters ===== */
const certFilterWrap = document.getElementById('certFilters');
if (certFilterWrap) {
  const certTiles = [...document.querySelectorAll('#certificateGrid .post')];

  certFilterWrap.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;

    certFilterWrap.querySelectorAll('button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const f = btn.dataset.filter;

    // Fade filter with defensive data-cat handling
    certTiles.forEach(t => {
      const cat = t.dataset.cat || '';              // <- guard
      if (f === 'all' || cat.includes(f)) {
        t.style.opacity = '0';
        setTimeout(() => {
          t.style.display = '';
          t.style.opacity = '1';
        }, 300);
      } else {
        t.style.opacity = '0';
        setTimeout(() => {
          t.style.display = 'none';
        }, 300);
      }
    });
  });
}


/* ===== Back to top ===== */
const back = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  const show = window.scrollY > 400;
  back.classList.toggle('show', show);
});
back.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

/* ===== Contact form (demo only) ===== */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', e => {
    e.preventDefault();
    alert('Thanks! This demo does not send email.');
  });
}

/* ===== Main Initialization ===== */
document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.remove('no-js');
  console.log('DOM fully loaded - Initializing all components');
  
  // --- Initialize Typewriter Effect ---
  typewriterElement = document.getElementById('typewriter');
  words = ['LEARNER', 'TECH ENTHUSIAST', 'STUDENT']; // Update global variable
  
  // Logo welcome text effect
  logoTextElement = document.getElementById('logoText');
  welcomeText = 'WELCOME'; // Update global variable
  
  // Initialize the tracking variable based on current scroll position
  const homeSection = document.getElementById('home');
  wasInHomeSection = homeSection && window.scrollY < homeSection.offsetHeight;
  
  // Start typewriter effects
  if (typewriterElement) typeWriter();
  if (logoTextElement) {
    // Initialize logo animation state - it should start running
    isLogoAnimationComplete = false;
    logoCharIndex = 0;
    isLogoDeleting = false;
    logoTypeWriter();
  }
  
  // Add scroll listener for logo visibility
  window.addEventListener('scroll', handleLogoVisibility);
  
  // --- Section Fade-In Animation ---
  const sections = document.querySelectorAll('.section');
  
  // Create intersection observer for smooth fade-in animations
  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Add appear class for smooth fade-in
        entry.target.classList.add('appear');
        
        // Special handling for contact section - make map visible but keep fade-in
        if (entry.target.id === 'contact') {
          const mapWrap = entry.target.querySelector('.map-wrap');
          if (mapWrap) {
            // Don't remove map animations, just optimize performance
            const iframe = mapWrap.querySelector('iframe');
            if (iframe) {
              iframe.style.transform = 'translateZ(0)';
              iframe.style.backfaceVisibility = 'hidden';
            }
          }
        }
      }
    });
  }, {
    root: null,
    threshold: 0.10, // Trigger when 10% of section is visible
    rootMargin: "0px 0px -10% 0px" // Trigger slightly before section fully comes into view
  });    
  // Start observing each section for fade-in animation
  sections.forEach(section => {
    sectionObserver.observe(section);
  });
   // Safety: ensure Certificates becomes visible even if the observer misses
const certSec = document.getElementById('certificates');
if (certSec) {
  const r = certSec.getBoundingClientRect();
  const inView = r.top < window.innerHeight * 0.85 && r.bottom > 0;
  if (inView) certSec.classList.add('appear');
}

 // Fallback: if IntersectionObserver ever misses #certificates while scrolling,
 // reveal it as soon as it enters the viewport.
window.addEventListener('scroll', () => {
  const sec = document.getElementById('certificates');
  if (!sec || sec.classList.contains('appear')) return;
    const r = sec.getBoundingClientRect();
        if (r.top < window.innerHeight * 0.85 && r.bottom > 0) {
             sec.classList.add('appear');
        }
}, { passive: true });
  // Special handling for map - make it visible immediately and optimize performance
  const contactSection = document.getElementById('contact');
  if (contactSection) {
    const mapWrap = contactSection.querySelector('.map-wrap');
    if (mapWrap) {
      // Ensure map appears immediately without transition
      mapWrap.style.opacity = '1';
      mapWrap.style.transform = 'none';
      mapWrap.style.transition = 'none';
      mapWrap.style.willChange = 'auto';
      
      // Optimize iframe performance
      const iframe = mapWrap.querySelector('iframe');
      if (iframe) {
        iframe.style.transform = 'translateZ(0)';
        iframe.style.backfaceVisibility = 'hidden';
      }
    }
  }
  
  // Start the typewriter effects - using functions defined at the top of file
//  typeWriter();
//  logoTypeWriter();
  
  // --- Certificate Lightbox  ---
  
  const lightbox = document.getElementById('certificateLightbox');
  const lightboxImage = document.getElementById('lightboxImage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const closeBtn = document.querySelector('#certificateLightbox .lightbox-close');
  
  if (lightbox && lightboxImage && lightboxCaption && closeBtn) {
    // Prevent links from opening when clicking on certificate tiles
    const certificateTiles = document.querySelectorAll('#certificateGrid .post');
    
    certificateTiles.forEach(tile => {
      tile.addEventListener('click', function(e) {
        e.preventDefault();
        
        const img = this.querySelector('img');
        const title = this.querySelector('.tile-content h3').textContent;
        
        // Set the image source and caption for the lightbox
        lightboxImage.src = img.src;
        lightboxImage.alt = img.alt;
        lightboxCaption.textContent = title;
        
        // Show the lightbox
        lightbox.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent scrolling when lightbox is open
      });
    });
    
    // Close lightbox when clicking the close button
    closeBtn.addEventListener('click', () => {
      lightbox.classList.remove('show');
      document.body.style.overflow = ''; // Re-enable scrolling
    });
    
    // Close lightbox when clicking outside the image
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        lightbox.classList.remove('show');
        document.body.style.overflow = '';
      }
    });
    
   
  }
  // --- Project Modal (open from project tiles) ---
   const projectModal = document.getElementById('projectModal');
   const projectClose = document.getElementById('projectClose');
   const projectTitle = document.getElementById('projectTitle');
   const projectSummary = document.getElementById('projectSummary');
   const projectGithub = document.getElementById('projectGithub');
   const projectTiles = document.querySelectorAll('.project-grid .tile');
 
   if (projectModal && projectClose && projectTitle && projectTiles.length) {
     projectTiles.forEach(tile => {
       tile.addEventListener('click', (e) => {
         e.preventDefault();
         projectTitle.textContent =
           tile.querySelector('.tile-content h3')?.textContent || 'Project';
         projectSummary.textContent = tile.dataset.summary || '';
         const gh = tile.dataset.github;
         if (gh) { projectGithub.href = gh; projectGithub.style.display = ''; }
         else { projectGithub.style.display = 'none'; }
         projectModal.classList.add('show');
         document.body.style.overflow = 'hidden';
       });
     });
 
     projectClose.addEventListener('click', () => {
       projectModal.classList.remove('show');
       document.body.style.overflow = '';
     });
     projectModal.addEventListener('click', (e) => {
       if (e.target === projectModal) {
         projectModal.classList.remove('show');
        document.body.style.overflow = '';
       }
     });
     
   }
   document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;

  // close certificate lightbox if open
  if (lightbox && lightbox.classList.contains('show')) {
    lightbox.classList.remove('show');
    document.body.style.overflow = '';
    return;
  }

  // close project modal if open
  if (projectModal && projectModal.classList.contains('show')) {
    projectModal.classList.remove('show');
    document.body.style.overflow = '';
  }
});

});
