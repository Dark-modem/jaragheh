/* ===========================================================
   جرقه | اسکریپت اصلی (وانیلا، بدون کتابخانه خارجی)
   =========================================================== */
(function () {
  'use strict';

  var faDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
  function fa(n){ return String(n).replace(/\d/g, function(d){ return faDigits[d]; }); }

  /* ---------- نمودار دونات ----------
     عناصر با کلاس .js-donut و دیتاهای زیر:
     data-value (۰..۱۰۰), data-color, data-track, data-label, data-center, data-sub */
  function buildDonut(el){
    var value = Math.max(0, Math.min(100, parseFloat(el.dataset.value || '0')));
    var size = 170, stroke = 14, r = (size - stroke) / 2, c = 2 * Math.PI * r;
    var color = el.dataset.color || '#3f82ff';
    var track = el.dataset.track || '#16213a';
    var offset = c * (1 - value / 100);
    var svgNS = 'http://www.w3.org/2000/svg';

    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + size + ' ' + size);
    svg.setAttribute('width', size); svg.setAttribute('height', size);

    var defs = document.createElementNS(svgNS, 'defs');
    var gid = 'g' + Math.random().toString(36).slice(2, 8);
    var grad = document.createElementNS(svgNS, 'linearGradient');
    grad.setAttribute('id', gid);
    grad.setAttribute('x1','0%'); grad.setAttribute('y1','0%');
    grad.setAttribute('x2','100%'); grad.setAttribute('y2','100%');
    var s1 = document.createElementNS(svgNS,'stop'); s1.setAttribute('offset','0%'); s1.setAttribute('stop-color', color);
    var s2 = document.createElementNS(svgNS,'stop'); s2.setAttribute('offset','100%'); s2.setAttribute('stop-color', el.dataset.color2 || color);
    grad.appendChild(s1); grad.appendChild(s2); defs.appendChild(grad); svg.appendChild(defs);

    var bg = document.createElementNS(svgNS, 'circle');
    bg.setAttribute('cx', size/2); bg.setAttribute('cy', size/2); bg.setAttribute('r', r);
    bg.setAttribute('fill','none'); bg.setAttribute('stroke', track); bg.setAttribute('stroke-width', stroke);
    svg.appendChild(bg);

    var fg = document.createElementNS(svgNS, 'circle');
    fg.setAttribute('cx', size/2); fg.setAttribute('cy', size/2); fg.setAttribute('r', r);
    fg.setAttribute('fill','none'); fg.setAttribute('stroke','url(#'+gid+')');
    fg.setAttribute('stroke-width', stroke); fg.setAttribute('stroke-linecap','round');
    fg.setAttribute('stroke-dasharray', c);
    fg.setAttribute('stroke-dashoffset', c);
    fg.setAttribute('transform','rotate(-90 '+(size/2)+' '+(size/2)+')');
    fg.style.transition = 'stroke-dashoffset 1.1s cubic-bezier(.3,1,.4,1)';
    svg.appendChild(fg);

    var wrap = document.createElement('div'); wrap.className = 'donut-wrap';
    wrap.appendChild(svg);
    var center = document.createElement('div'); center.className = 'donut-center';
    center.innerHTML = '<b>' + (el.dataset.center || fa(Math.round(value)) + '٪') + '</b>' +
                       '<span>' + (el.dataset.sub || '') + '</span>';
    wrap.appendChild(center);
    el.appendChild(wrap);

    // انیمیشن پر شدن
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){ fg.setAttribute('stroke-dashoffset', offset); });
    });
  }

  /* ---------- گراف پینگ هیرو ---------- */
  function buildPing(svg){
    var w = 300, h = 120, svgNS = 'http://www.w3.org/2000/svg';
    svg.setAttribute('viewBox','0 0 '+w+' '+h);
    var pts = [88,92,80,84,40,36,30,33,28,26,29,27];
    var max = 100, step = w / (pts.length - 1);
    var d = '', area = 'M 0 ' + h + ' ';
    pts.forEach(function(p,i){
      var x = i*step, y = h - (p/max)*h*0.85 - 8;
      d += (i===0?'M':'L') + ' ' + x.toFixed(1) + ' ' + y.toFixed(1) + ' ';
      area += 'L ' + x.toFixed(1) + ' ' + y.toFixed(1) + ' ';
    });
    area += 'L ' + w + ' ' + h + ' Z';

    var grad = '<linearGradient id="pg" x1="0" y1="0" x2="0" y2="1">' +
               '<stop offset="0%" stop-color="rgba(55,219,255,.35)"/>' +
               '<stop offset="100%" stop-color="rgba(55,219,255,0)"/></linearGradient>';
    svg.innerHTML =
      '<defs>'+grad+'</defs>' +
      '<path d="'+area+'" fill="url(#pg)"/>' +
      '<path d="'+d+'" fill="none" stroke="#37dbff" stroke-width="2.5" ' +
        'stroke-linecap="round" stroke-linejoin="round" ' +
        'style="filter:drop-shadow(0 0 6px rgba(55,219,255,.5))"/>';
    var path = svg.querySelectorAll('path')[1];
    var len = path.getTotalLength();
    path.style.strokeDasharray = len; path.style.strokeDashoffset = len;
    path.style.transition = 'stroke-dashoffset 1.8s ease';
    requestAnimationFrame(function(){ requestAnimationFrame(function(){ path.style.strokeDashoffset = 0; }); });
  }

  /* ---------- منوی موبایل سایت ---------- */
  function bindMenu(){
    var t = document.querySelector('.menu-toggle[data-target="navlinks"]');
    var m = document.getElementById('navlinks');
    if(t && m){ t.addEventListener('click', function(){ m.classList.toggle('open'); }); }
  }

  /* ---------- سایدبار پنل (موبایل) ---------- */
  function bindSidebar(){
    var t = document.querySelector('.menu-toggle[data-target="sidebar"]');
    var s = document.getElementById('sidebar');
    var b = document.getElementById('sidebarBackdrop');
    function close(){ if(s)s.classList.remove('open'); if(b)b.classList.remove('open'); }
    if(t && s){ t.addEventListener('click', function(){ s.classList.toggle('open'); if(b)b.classList.toggle('open'); }); }
    if(b){ b.addEventListener('click', close); }
  }

  /* ---------- جمع‌کردن سایدبار (دسکتاپ) ---------- */
  function bindSidebarCollapse(){
    var s = document.getElementById('sidebar');
    var btn = document.getElementById('sidebarCollapse');
    if(!s || !btn) return;
    // بازگردانی وضعیت ذخیره‌شده
    try {
      if(localStorage.getItem('sidebar-collapsed') === '1'){ s.classList.add('collapsed'); }
    } catch(e){}
    btn.addEventListener('click', function(e){
      e.preventDefault(); e.stopPropagation();
      s.classList.toggle('collapsed');
      try {
        localStorage.setItem('sidebar-collapsed', s.classList.contains('collapsed') ? '1' : '0');
      } catch(e){}
    });
  }

  /* ---------- مودال ---------- */
  window.openModal = function(id){ var m = document.getElementById(id); if(m) m.classList.add('open'); };
  window.closeModal = function(id){ var m = document.getElementById(id); if(m) m.classList.remove('open'); };
  function bindModals(){
    document.querySelectorAll('.modal-overlay').forEach(function(o){
      o.addEventListener('click', function(ev){ if(ev.target === o) o.classList.remove('open'); });
    });
    document.querySelectorAll('[data-open-modal]').forEach(function(btn){
      btn.addEventListener('click', function(){ openModal(btn.dataset.openModal); });
    });
  }

  /* ---------- انتخاب درگاه ---------- */
  function bindGateways(){
    var opts = document.querySelectorAll('.gateway-opt');
    if(!opts.length) return;
    function refresh(){
      opts.forEach(function(o){
        var r = o.querySelector('input[type=radio]');
        o.classList.toggle('selected', r && r.checked);
      });
      var card = document.querySelector('input[name="gateway"][value="card"]');
      var note = document.getElementById('cardNote');
      if(note){ note.style.display = (card && card.checked) ? 'flex' : 'none'; }
    }
    opts.forEach(function(o){
      o.addEventListener('click', function(){
        var r = o.querySelector('input[type=radio]'); if(r){ r.checked = true; refresh(); }
      });
    });
    document.querySelectorAll('input[name="gateway"]').forEach(function(r){ r.addEventListener('change', refresh); });
    refresh();
  }

  /* ---------- ارسال و تایید کد ----------
     دکمه با data-send-code="email|phone" کد می‌فرستد و تایمر شمارش معکوس می‌گذارد */
  function bindCodes(){
    document.querySelectorAll('[data-send-code]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var type = btn.dataset.sendCode;
        btn.disabled = true; btn.textContent = 'در حال ارسال...';
        fetch('api/send_code.php', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body:'type='+encodeURIComponent(type)+'&csrf='+encodeURIComponent(window.CSRF||'')
        }).then(function(r){return r.json();}).then(function(res){
          var box = document.getElementById('codebox-'+type);
          if(box) box.style.display = 'block';
          if(res.dev_code){
            var dc = document.getElementById('devcode-'+type);
            if(dc){ dc.style.display='block'; dc.querySelector('b').textContent = fa(res.dev_code); }
          }
          startTimer(btn, 90, type);
        }).catch(function(){
          btn.disabled = false; btn.textContent = 'ارسال کد';
          alert('ارسال کد ناموفق بود. دوباره تلاش کنید.');
        });
      });
    });
  }
  function startTimer(btn, sec, type){
    var n = sec;
    btn.textContent = 'ارسال مجدد (' + fa(n) + ')';
    var iv = setInterval(function(){
      n--;
      if(n <= 0){ clearInterval(iv); btn.disabled = false; btn.textContent = 'ارسال مجدد کد'; }
      else { btn.textContent = 'ارسال مجدد (' + fa(n) + ')'; }
    }, 1000);
  }

  function bindFaq(){
    document.querySelectorAll('.faq-q').forEach(function(q){
      q.addEventListener('click', function(){
        var item = q.closest('.faq-item');
        var wasOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(function(o){ o.classList.remove('open'); });
        if(!wasOpen){ item.classList.add('open'); }
      });
    });
  }

  /* ---------- اجرا ---------- */
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.js-donut').forEach(buildDonut);
    document.querySelectorAll('.js-ping').forEach(buildPing);
    bindMenu(); bindSidebar(); bindSidebarCollapse(); bindModals(); bindGateways(); bindCodes(); bindFaq();
  });
})();
