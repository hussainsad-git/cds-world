/* CDS World public site behaviour (jQuery). No inline scripts: the site's CSP allows only self-hosted scripts. */
(function ($) {
  'use strict';

  // Ticker: duplicate items once for a seamless loop (skipped for reduced motion).
  var $tick = $('.ticker-list');
  if ($tick.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    $tick.append($tick.children().clone().attr('aria-hidden', 'true').find('a').attr('tabindex', '-1').end());
  }

  // Video facade: the player iframe loads only after a click.
  $(document).on('click', '.video-play', function () {
    var $f = $(this).closest('.video-facade');
    var src = $f.data('embed');
    if (!src) { return; }
    var $i = $('<iframe>', {
      src: src, title: $f.data('title') || 'Video', allowfullscreen: true,
      allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
      referrerpolicy: 'strict-origin-when-cross-origin'
    });
    $f.empty().append($i);
  });

  // Copy link
  $(document).on('click', '.copy-link', function () {
    var $b = $(this), url = $b.data('url');
    var done = function () { var t = $b.text(); $b.text('Copied'); setTimeout(function () { $b.text(t); }, 1600); };
    if (navigator.clipboard) { navigator.clipboard.writeText(url).then(done); }
    else { var $t = $('<input>').val(url).appendTo('body').select(); document.execCommand('copy'); $t.remove(); done(); }
  });

  // Lightbox. Images inside article, event and page bodies arrive as raw HTML with no
  // link around them, so each one is wrapped before GLightbox scans the page. Images
  // that are already links are left alone, as are icon-sized ones.
  if (window.GLightbox) {
    $('.prose-cds img').each(function () {
      var $img = $(this), w = parseInt($img.attr('width'), 10);
      if ($img.closest('a').length || (w && w < 160) || /emoji|smiley/.test(this.className)) { return; }
      var best = this.getAttribute('src') || '', bestW = 0;
      ($img.attr('srcset') || '').split(',').forEach(function (c) {
        var m = $.trim(c).match(/^(\S+)\s+(\d+)w$/);
        if (m && parseInt(m[2], 10) > bestW) { bestW = parseInt(m[2], 10); best = m[1]; }
      });
      if (!best) { return; }
      var $a = $('<a class="glightbox" data-gallery="body" data-type="image" aria-label="Enlarge image"></a>').attr('href', best);
      var cap = $.trim($img.closest('figure').find('figcaption').text());
      if (cap) { $a.attr('data-description', cap); }
      $img.wrap($a);
    });
  }
  if (window.GLightbox && $('.glightbox').length) { GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true }); }

  // Newsletter: progressive enhancement (works without JS as a normal POST).
  $(document).on('submit', '.newsletter-form', function (e) {
    var $form = $(this), $msg = $form.find('.newsletter-msg'), $btn = $form.find('button[type=submit]');
    e.preventDefault();
    $btn.prop('disabled', true);
    $.ajax({ url: $form.attr('action'), method: 'POST', data: $form.serialize(), dataType: 'json', headers: { 'Accept': 'application/json' } })
      .done(function (r) { $msg.html($('<span class="text-ok">').text(r.message)); $form[0].reset(); })
      .fail(function (x) {
        var m = (x.responseJSON && x.responseJSON.message) || 'Something went wrong. Please try again.';
        $msg.html($('<span class="text-err">').text(m));
      })
      .always(function () { $btn.prop('disabled', false); });
  });

  // Popups: frequency, device and date rules are enforced here; targeting and schedule are enforced server-side.
  function store(kind) { try { return kind === 'session' ? window.sessionStorage : window.localStorage; } catch (e) { return null; } }
  $('.cds-popup').each(function () {
    var $p = $(this), id = $p.data('id'), freq = String($p.data('frequency')), days = parseInt($p.data('days'), 10) || 1;
    var mobile = window.matchMedia('(max-width: 767px)').matches;
    if ((mobile && !$p.data('mobile')) || (!mobile && !$p.data('desktop'))) { return; }
    var key = 'cds_popup_' + id + '_' + $p.data('version');
    var ls = store('local'), ss = store('session');
    var last = ls ? parseInt(ls.getItem(key) || '0', 10) : 0;
    if (freq === 'once_per_session' && ss && ss.getItem(key)) { return; }
    if (freq === 'once_per_visitor' && last) { return; }
    if (freq === 'every_n_days' && last && (Date.now() - last) < days * 864e5) { return; }

    setTimeout(function () {
      $p.prop('hidden', false).find('.cds-popup-close').trigger('focus');
      if (ls) { ls.setItem(key, String(Date.now())); }
      if (ss) { ss.setItem(key, '1'); }
    }, (parseInt($p.data('delay'), 10) || 0) * 1000);
  });
  $(document).on('click', '[data-popup-close]', function () { $(this).closest('.cds-popup').prop('hidden', true); });
  $(document).on('keydown', function (e) { if (e.key === 'Escape') { $('.cds-popup:visible').prop('hidden', true); } });
})(jQuery);
