(function() {
  var $;

  $ = jQuery;

  $(document).on('ready', function() {
    var currentTimeout, matcher;
    matcher = new RegExp('\u200C([0-9a-fA-F]{32})\u200C([^\u200C]*)\u200C\u200C', 'g');
    $('html :contains("\u200C\u200C")').each(function() {
      var $this;
      $this = $(this);
      return $.each(this.childNodes, function() {
        var lastLastIndex, matchArray, newSpan, node, orig_text, parentNode, upToLength;
        node = this;
        if (node.nodeType === Node.TEXT_NODE) {
          parentNode = node.parentNode;
          orig_text = node.nodeValue;
          lastLastIndex = 0;
          upToLength = 0;
          while ((matchArray = matcher.exec(orig_text)) !== null) {
            if (upToLength + matchArray[0].length !== matcher.lastIndex) {
              parentNode.insertBefore(document.createTextNode(orig_text.substring(upToLength, matcher.lastIndex - matchArray[0].length)), node);
            }
            newSpan = document.createElement('span');
            newSpan.appendChild(document.createTextNode(matchArray[2]));
            newSpan.setAttribute('class', 'i18n-tk-string');
            newSpan.setAttribute('data-strid', matchArray[1]);
            parentNode.insertBefore(newSpan, node);
            lastLastIndex = matcher.lastIndex;
            upToLength += matchArray[0].length;
          }
          if (lastLastIndex !== 0 && upToLength !== 0) {
            if (upToLength !== orig_text.length) {
              parentNode.insertBefore(document.createTextNode(orig_text.substring(upToLength)), node);
            }
            parentNode.removeChild(node);
          }
        }
        return true;
      });
    });
    currentTimeout = -1;
    $('html').on('mouseenter', '.i18n-tk-string', {}, function() {
      var $this, popover, string_data;
      $this = $(this);
      string_data = document.WpI18nToolkit[$this.data('strid')];
      console.log("You hovered over a translated string: " + string_data.translated + " (" + string_data.domain + " domain)");
      if ((popover = $('#i18n-tk-popover')).length === 0) {
        popover = $('<div id="i18n-tk-popover"></div>').appendTo($('body'));
      }
      popover.css({
        top: $this.offset().top,
        left: $this.offset().left + (parseInt($this.css('width')) / 2)
      });
      popover.html(WPI18NJST['js/src/i18n-tk-popover.hbs'](string_data));
      if (currentTimeout !== -1) {
        window.clearTimeout(currentTimeout);
        currentTimeout = -1;
      }
      return window.setTimeout(function() {
        return popover.addClass('shown');
      }, 1);
    });
    $('html').on('mouseenter', '#i18n-tk-popover', {}, function() {
      if (currentTimeout !== -1) {
        window.clearTimeout(currentTimeout);
        return currentTimeout = -1;
      }
    });
    return $('html').on('mouseleave', '.i18n-tk-string, #i18n-tk-popover', {}, function() {
      return currentTimeout = window.setTimeout(function() {
        return $('#i18n-tk-popover').removeClass('shown');
      }, 500);
    });
  });

}).call(this);
