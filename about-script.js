document.addEventListener('DOMContentLoaded', () => {
  // 1) Fade-in setup (unchanged)
  const fadeElements = document.querySelectorAll('.fade-in');
  const observerOptions = { threshold: 0.1 };
  const fadeObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        obs.unobserve(entry.target);
      }
    });
  }, observerOptions);
  fadeElements.forEach(el => {
    el.style.animationPlayState = 'paused';
    fadeObserver.observe(el);
  });

  // 2) Fetch About data
  fetch('about-fetch.php', { credentials: 'include' })
    .then(res => res.json())
    .then(json => {
      if (!json.success) throw new Error(json.message || 'Failed to load about data');
      const { history, contacts, social_media, faqs } = json.data;

      // 2a) History
      const historyP = document.querySelector('#history p');
      if (historyP) historyP.innerHTML = history.replace(/\n/g, '<br>');

      // 2b) Contact Info (now loops all)
      const contactList = document.querySelector('#contact ul');
      if (contactList) {
        contactList.innerHTML = '';
        contacts.forEach(({ type, value }) => {
          const li = document.createElement('li');
          if (type.toLowerCase() === 'email') {
            li.innerHTML = `<strong>${type}:</strong> <a href="mailto:${value}">${value}</a>`;
          } else {
            li.innerHTML = `<strong>${type}:</strong> ${value}`;
          }
          contactList.appendChild(li);
        });
      }

      // 2c) Social Media
      const smContainer = document.querySelector('.social-media .social-buttons');
      if (smContainer) {
        smContainer.innerHTML = '';
        social_media.forEach(({ platform, url }) => {
          const a = document.createElement('a');
          a.className = 'social-btn';
          a.href = url;
          a.textContent = platform;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          smContainer.appendChild(a);
        });
      }

      // 2d) FAQs
      const faqContainer = document.querySelector('#faqs .faq-list');
      if (faqContainer) {
        faqContainer.innerHTML = '';
        faqs.forEach(({ question, answer }) => {
          const details = document.createElement('details');
          const summary = document.createElement('summary');
          summary.textContent = question;
          const p = document.createElement('p');
          p.textContent = answer;
          details.append(summary, p);
          faqContainer.appendChild(details);
        });
      }
    })
    .catch(err => {
      console.error('About-page load error:', err);
    });
});
