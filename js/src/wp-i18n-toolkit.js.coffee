# CoffeeScript compiles us into a closure, so we're safe doing this.
$ = jQuery

$(document).on 'ready', () ->
  matcher = new RegExp '\u200C([0-9a-fA-F]{32})\u200C([^\u200C]*)\u200C\u200C', 'g'

  $('html :contains("\u200C\u200C")').each () ->
    # Match whole strings.
    $this = $(this)

    $.each this.childNodes, () ->
      node = this

      if node.nodeType == Node.TEXT_NODE
        parentNode = node.parentNode
        orig_text = node.nodeValue

        lastLastIndex = 0
        upToLength = 0

        while (matchArray = matcher.exec(orig_text)) != null
          # check for prepending text
          if upToLength + matchArray[0].length != matcher.lastIndex
            parentNode.insertBefore document.createTextNode(orig_text.substring(upToLength, (matcher.lastIndex - matchArray[0].length))), node

          newSpan = document.createElement 'span'
          newSpan.appendChild document.createTextNode(matchArray[2])
          newSpan.setAttribute 'class', 'i18n-tk-string'
          newSpan.setAttribute 'data-strid', matchArray[1]

          parentNode.insertBefore newSpan, node

          lastLastIndex = matcher.lastIndex
          upToLength += matchArray[0].length

        if lastLastIndex != 0 and upToLength != 0
          # check for postpending text
          if upToLength != orig_text.length
            parentNode.insertBefore document.createTextNode(orig_text.substring(upToLength)), node

          parentNode.removeChild node

      true

  currentTimeout = -1

  $('html').on 'mouseenter', '.i18n-tk-string', {}, () ->
    $this = $ this
    string_data = document.WpI18nToolkit[$this.data 'strid']
    console.log "You hovered over a translated string: #{ string_data.translated } (#{ string_data.domain } domain)"

    if (popover = $ '#i18n-tk-popover').length == 0
      popover = $('<div id="i18n-tk-popover"></div>').appendTo $('body')

    popover.css
      top: $this.offset().top,
      left: $this.offset().left + (parseInt($this.css('width')) / 2)

    popover.html WPI18NJST['js/src/i18n-tk-popover.hbs'](string_data)

    if currentTimeout != -1
      window.clearTimeout currentTimeout
      currentTimeout = -1

    window.setTimeout(
      -> popover.addClass 'shown'
      1
    )

  $('html').on 'mouseenter', '#i18n-tk-popover', {}, () ->
    if currentTimeout != -1
      window.clearTimeout currentTimeout
      currentTimeout = -1

  $('html').on 'mouseleave', '.i18n-tk-string, #i18n-tk-popover', {}, () ->
    currentTimeout = window.setTimeout(
      -> $('#i18n-tk-popover').removeClass 'shown'
      500
    )
