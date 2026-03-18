/**
 * Oregon Tires — Gallery Video Renderer
 * Handles YouTube/Vimeo embeds with lazy loading and responsive layout.
 */
(function() {
  'use strict';

  /**
   * Extract video ID and provider from a URL.
   * Supports YouTube (youtube.com, youtu.be) and Vimeo.
   */
  function parseVideoUrl(url) {
    if (!url) return null;

    // YouTube
    var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (match) return { provider: 'youtube', id: match[1] };

    // Vimeo
    match = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
    if (match) return { provider: 'vimeo', id: match[1] };

    return null;
  }

  /**
   * Get thumbnail URL for a video.
   */
  function getThumbnail(video) {
    if (video.provider === 'youtube') {
      return 'https://img.youtube.com/vi/' + video.id + '/hqdefault.jpg';
    }
    // Vimeo thumbnails require an API call; return placeholder
    return null;
  }

  /**
   * Build a responsive embed iframe.
   */
  function buildEmbed(video) {
    var wrapper = document.createElement('div');
    wrapper.className = 'relative w-full overflow-hidden rounded-lg';
    wrapper.style.paddingBottom = '56.25%'; // 16:9

    var iframe = document.createElement('iframe');
    iframe.className = 'absolute inset-0 w-full h-full';
    iframe.setAttribute('loading', 'lazy');
    iframe.setAttribute('allowfullscreen', '');
    iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');

    if (video.provider === 'youtube') {
      iframe.src = 'https://www.youtube-nocookie.com/embed/' + video.id + '?rel=0';
    } else if (video.provider === 'vimeo') {
      iframe.src = 'https://player.vimeo.com/video/' + video.id + '?dnt=1';
    }

    wrapper.appendChild(iframe);
    return wrapper;
  }

  /**
   * Create an SVG play icon using DOM methods (no innerHTML).
   */
  function createPlayIcon() {
    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('class', 'w-8 h-8 text-white ml-1');
    svg.setAttribute('fill', 'currentColor');
    svg.setAttribute('viewBox', '0 0 24 24');
    var path = document.createElementNS(svgNS, 'path');
    path.setAttribute('d', 'M8 5v14l11-7z');
    svg.appendChild(path);
    return svg;
  }

  /**
   * Build a click-to-play thumbnail (lazy video loading).
   */
  function buildLazyVideo(video) {
    var container = document.createElement('div');
    container.className = 'relative w-full overflow-hidden rounded-lg cursor-pointer group';
    container.style.paddingBottom = '56.25%';

    var thumb = getThumbnail(video);
    if (thumb) {
      var img = document.createElement('img');
      img.src = thumb;
      img.alt = 'Video thumbnail';
      img.className = 'absolute inset-0 w-full h-full object-cover';
      img.loading = 'lazy';
      container.appendChild(img);
    } else {
      var placeholder = document.createElement('div');
      placeholder.className = 'absolute inset-0 w-full h-full bg-gray-800 flex items-center justify-center';
      container.appendChild(placeholder);
    }

    // Play button overlay (built with DOM methods)
    var playBtn = document.createElement('div');
    playBtn.className = 'absolute inset-0 flex items-center justify-center';
    var circle = document.createElement('div');
    circle.className = 'w-16 h-16 bg-black/70 rounded-full flex items-center justify-center group-hover:bg-green-700/90 transition-colors';
    circle.appendChild(createPlayIcon());
    playBtn.appendChild(circle);
    container.appendChild(playBtn);

    container.addEventListener('click', function() {
      var embed = buildEmbed(video);
      container.parentNode.replaceChild(embed, container);
    });

    return container;
  }

  /**
   * Initialize all video elements in gallery.
   * Call with elements that have data-video-url attribute.
   */
  function initGalleryVideos(selector) {
    var elements = document.querySelectorAll(selector || '[data-video-url]');
    elements.forEach(function(el) {
      var url = el.getAttribute('data-video-url');
      var video = parseVideoUrl(url);
      if (!video) return;

      var player = buildLazyVideo(video);
      el.appendChild(player);
    });
  }

  // Expose globally
  window.GalleryVideo = {
    parseVideoUrl: parseVideoUrl,
    getThumbnail: getThumbnail,
    buildEmbed: buildEmbed,
    buildLazyVideo: buildLazyVideo,
    init: initGalleryVideos,
  };
})();
