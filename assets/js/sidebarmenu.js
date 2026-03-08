$(function () {
  "use strict";

  var url = window.location + "";
  var path = url.replace(
    window.location.protocol + "//" + window.location.host + "/",
    ""
  );
  var element = $("ul#sidebarnav a").filter(function () {
    return this.href === url || this.href === path;
  });

  // === helper: get the scrolling element (works with SimpleBar) ===
  function getSidebarScrollEl() {
    var container = document.querySelector('.scroll-sidebar');
    if (!container) return null;
    var simpleWrapper = container.querySelector('.simplebar-content-wrapper');
    return simpleWrapper || container;
  }

  // small debounce helper
  function debounce(fn, wait) {
    var t;
    return function () {
      var ctx = this, args = arguments;
      clearTimeout(t);
      t = setTimeout(function () {
        fn.apply(ctx, args);
      }, wait);
    };
  }

  // restore saved scroll (if any)
  function restoreSidebarScroll() {
    var el = getSidebarScrollEl();
    if (!el) return;
    var saved = sessionStorage.getItem('sidebar-scroll');
    if (saved !== null) {
      try { el.scrollTop = parseInt(saved, 10); } catch (e) {}
    }
  }

  // save current scroll position
  function saveSidebarScroll() {
    var el = getSidebarScrollEl();
    if (!el) return;
    sessionStorage.setItem('sidebar-scroll', String(el.scrollTop));
  }

  // center active link (only if there's no saved scroll)
  function scrollActiveIntoView() {
    var el = getSidebarScrollEl();
    if (!el) return;

    var active = $("#sidebarnav a.active")[0];
    if (!active) return;

    var saved = sessionStorage.getItem('sidebar-scroll');
    if (saved !== null) {
      // restore saved and return (we don't override saved scroll)
      try { el.scrollTop = parseInt(saved, 10); } catch (e) {}
      return;
    }

    // compute offsetTop relative to scroller
    var top = 0;
    var node = active;
    // accumulate offsetTop until we reach the scroll container
    while (node && node !== el) {
      top += node.offsetTop || 0;
      node = node.offsetParent;
    }
    var center = Math.max(0, top - (el.clientHeight / 2) + (active.offsetHeight / 2));

    // use smooth if you like, 'auto' is fine on load
    if (typeof el.scrollTo === "function") {
      el.scrollTo({ top: center, behavior: "auto" });
    } else {
      el.scrollTop = center;
    }
  }

  // Persist scroll as user scrolls (throttled)
  (function attachScrollSaver() {
    var el = getSidebarScrollEl();
    if (!el) return;
    $(el).on('scroll', debounce(saveSidebarScroll, 120));
    // also save before unload
    $(window).on('beforeunload', saveSidebarScroll);
  })();

  // === original active selection + parents logic (kept but slightly adapted) ===
  element.parentsUntil(".sidebar-nav").each(function (index) {
    if ($(this).is("li") && $(this).children("a").length !== 0) {
      $(this).children("a").addClass("active");
      $(this).parent("ul#sidebarnav").length === 0
        ? $(this).addClass("active")
        : $(this).addClass("selected");
    } else if (!$(this).is("ul") && $(this).children("a").length === 0) {
      $(this).addClass("selected");
    } else if ($(this).is("ul")) {
      $(this).addClass("in");
    }
  });

  element.addClass("active");

  // after we set the active item, restore or center into view
  // small timeout to ensure DOM/CSS has finalized and SimpleBar (if present) initialized
  setTimeout(function () {
    // restore saved or center on active
    scrollActiveIntoView();
  }, 50);

  // === click handlers (adapted to also scroll/save after clicking) ===
  $("#sidebarnav a").on("click", function (e) {
    if (!$(this).hasClass("active")) {
      // hide any open menus and remove other classes
      $("ul", $(this).parents("ul:first")).removeClass("in");
      $("a", $(this).parents("ul:first")).removeClass("active");

      // open our new menu and add the open class
      $(this).next("ul").addClass("in");
      $(this).addClass("active");
    } else if ($(this).hasClass("active")) {
      $(this).removeClass("active");
      $(this).parents("ul:first").removeClass("active");
      $(this).next("ul").removeClass("in");
    }

    // small delay then ensure active is visible and save scroll
    setTimeout(function () {
      scrollActiveIntoView();
      saveSidebarScroll();
    }, 30);
  });

  $("#sidebarnav >li >a.has-arrow").on("click", function (e) {
    e.preventDefault();
  });

  // If you want manual control (e.g., SPA route change), expose a global:
  window.refreshSidebarActiveAndScroll = function () {
    // re-run parent-opening logic for current active item
    var newElement = $("ul#sidebarnav a").filter(function () {
      return this.href === window.location + "" || this.href === window.location.pathname;
    });
    if (newElement && newElement.length) {
      $("ul#sidebarnav a").removeClass('active');
      newElement.addClass('active');
      // open parents
      newElement.parentsUntil(".sidebar-nav").each(function () {
        if ($(this).is("ul")) $(this).addClass("in");
        if ($(this).is("li") && $(this).children("a").length !== 0) $(this).children("a").addClass("active");
      });
    }
    // scroll after slight delay
    setTimeout(scrollActiveIntoView, 40);
  };
});
