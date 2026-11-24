// Client-side interactivity: date rules, live cost, AJAX availability, form validation.
document.addEventListener('DOMContentLoaded', () => {
  const ci = document.getElementById('check_in'), co = document.getElementById('check_out');
  const form = document.getElementById('bookForm');
  const iso = d => d.toISOString().slice(0, 10);

  // Same seasonal rule as total_cost() in config.php (Dec-Mar = +20%).
  function totalCost(price, a, b) {
    let d = new Date(a + 'T00:00:00Z'), end = new Date(b + 'T00:00:00Z'), t = 0;
    while (d < end) { t += price * ([12, 1, 2, 3].includes(d.getUTCMonth() + 1) ? 1.2 : 1); d.setUTCDate(d.getUTCDate() + 1); }
    return t;
  }
  function update() {
    if (!form || !ci.value || !co.value || co.value <= ci.value) return;
    const n = Math.round((new Date(co.value) - new Date(ci.value)) / 864e5);
    document.getElementById('nights').textContent = n + (n === 1 ? ' night' : ' nights');
    document.getElementById('cost').textContent = 'Estimated total: LKR ' + Math.round(totalCost(+form.dataset.price, ci.value, co.value)).toLocaleString();
    const out = document.getElementById('avail');
    fetch(`check_availability.php?room_id=${form.dataset.room}&check_in=${ci.value}&check_out=${co.value}`)
      .then(r => r.json()).then(j => { out.textContent = j.message; out.className = 'small mb-0 ' + (j.ok ? 'text-success' : 'text-danger'); })
      .catch(() => { out.textContent = ''; });
  }
  if (ci && co) {
    ci.min = iso(new Date());
    const sync = () => {
      if (ci.value) { const d = new Date(ci.value + 'T00:00:00Z'); d.setUTCDate(d.getUTCDate() + 1); co.min = iso(d); if (co.value && co.value < co.min) co.value = ''; }
      update();
    };
    ci.addEventListener('change', sync); co.addEventListener('change', update); sync();
  }

  // Validation for every form with class js-validate; messages shown with DOM manipulation.
  document.querySelectorAll('form.js-validate').forEach(f => f.addEventListener('submit', e => {
    const errs = [], val = n => (f.querySelector(`[name=${n}]`) || {}).value || '';
    f.querySelectorAll('[required]').forEach(i => { if (!i.value.trim()) errs.push((i.dataset.label || i.name) + ' is required.'); });
    if (val('email') && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val('email'))) errs.push('Enter a valid email address.');
    if (val('contact') && !/^\+?[0-9 \-]{7,15}$/.test(val('contact'))) errs.push('Enter a valid contact number (7-15 digits).');
    if (f.hasAttribute('data-register')) {
      if (val('password').length < 8) errs.push('Password must be at least 8 characters.');
      if (val('password') !== val('confirm')) errs.push('Passwords do not match.');
    }
    if (ci && co && ci.value && co.value && co.value <= ci.value) errs.push('Check-out must be after check-in.');
    if (f.dataset.max && +val('guests') > +f.dataset.max) errs.push('This room sleeps up to ' + f.dataset.max + ' guests.');
    const box = f.querySelector('.js-errors');
    if (box) box.replaceChildren();
    if (errs.length && box) {
      e.preventDefault();
      const div = document.createElement('div'); div.className = 'alert alert-danger py-2'; div.setAttribute('role', 'alert');
      errs.forEach(m => { const p = document.createElement('div'); p.textContent = m; div.appendChild(p); });
      box.appendChild(div); box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }));
});
