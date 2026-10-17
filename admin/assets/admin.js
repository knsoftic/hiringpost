/* Admin panel scripts: rich text editor, image previews, counters, delete confirmation. */
(function () {
    'use strict';

    /* ---------- Rich text editor ---------- */

    document.querySelectorAll('[data-editor]').forEach(initEditor);

    function initEditor(wrap) {
        var area = wrap.querySelector('.editor-area');
        var source = wrap.querySelector('.editor-source');
        var toolbar = wrap.querySelector('.editor-toolbar');
        var fileInput = wrap.querySelector('.editor-file');
        var status = wrap.querySelector('.editor-status');
        var form = wrap.closest('form');
        var sourceMode = false;
        var savedRange = null;

        area.innerHTML = source.value;
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* older browsers */ }

        function sync() {
            if (!sourceMode) source.value = area.innerHTML.trim() === '<p><br></p>' ? '' : area.innerHTML;
        }

        function saveRange() {
            var sel = window.getSelection();
            if (sel.rangeCount && area.contains(sel.getRangeAt(0).commonAncestorContainer)) {
                savedRange = sel.getRangeAt(0).cloneRange();
            }
        }

        function restoreRange() {
            area.focus();
            if (savedRange) {
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(savedRange);
            }
        }

        function exec(cmd, value) {
            restoreRange();
            document.execCommand(cmd, false, value || null);
            saveRange();
            sync();
            updateState();
        }

        function updateState() {
            toolbar.querySelectorAll('[data-cmd]').forEach(function (btn) {
                var cmd = btn.dataset.cmd;
                var on = false;
                try {
                    if (cmd === 'formatBlock') {
                        on = String(document.queryCommandValue('formatBlock')).toLowerCase() === btn.dataset.value;
                    } else if (/^(bold|italic|underline|strikeThrough|insertUnorderedList|insertOrderedList)$/.test(cmd)) {
                        on = document.queryCommandState(cmd);
                    }
                } catch (e) { /* ignore */ }
                if (cmd !== 'source') btn.classList.toggle('is-active', on && !sourceMode);
            });
        }

        ['keyup', 'mouseup'].forEach(function (evt) {
            area.addEventListener(evt, function () { saveRange(); updateState(); });
        });
        area.addEventListener('input', sync);

        // Keep the text selection when clicking toolbar buttons
        toolbar.addEventListener('mousedown', function (e) {
            if (e.target.closest('button')) e.preventDefault();
        });

        toolbar.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var cmd = btn.dataset.cmd;

            if (cmd === 'source') return toggleSource(btn);
            if (sourceMode) return;

            switch (cmd) {
                case 'formatBlock':
                    exec('formatBlock', '<' + btn.dataset.value + '>');
                    break;
                case 'link':
                    saveRange();
                    var href = prompt('Link URL', 'https://');
                    if (href && href !== 'https://') exec('createLink', href.trim());
                    break;
                case 'image':
                    saveRange();
                    fileInput.click();
                    break;
                case 'video':
                    saveRange();
                    var videoUrl = prompt('Paste a YouTube or Vimeo URL');
                    if (!videoUrl) break;
                    var src = embedSrc(videoUrl);
                    if (src) exec('insertHTML', '<p><iframe src="' + src + '" title="Video" allowfullscreen></iframe></p><p><br></p>');
                    else alert('Only YouTube and Vimeo links can be embedded.');
                    break;
                default:
                    exec(cmd);
            }
        });

        // Paste as clean text (keeps paragraphs and line breaks)
        area.addEventListener('paste', function (e) {
            var data = e.clipboardData || window.clipboardData;
            if (!data) return;
            e.preventDefault();
            var text = data.getData('text/plain');
            if (text.indexOf('\n') === -1) {
                document.execCommand('insertText', false, text);
            } else {
                var html = text.trim().split(/\r?\n\s*\r?\n/).map(function (para) {
                    return '<p>' + escapeHtml(para).replace(/\r?\n/g, '<br>') + '</p>';
                }).join('');
                document.execCommand('insertHTML', false, html);
            }
            sync();
        });

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;
            var data = new FormData();
            data.append('image', file);
            data.append('csrf', wrap.dataset.csrf);
            status.hidden = false;

            fetch(wrap.dataset.upload, { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (res.url) exec('insertImage', res.url);
                    else alert(res.error || 'Image upload failed.');
                })
                .catch(function () { alert('Image upload failed. Please try again.'); })
                .then(function () {
                    status.hidden = true;
                    fileInput.value = '';
                });
        });

        function toggleSource(btn) {
            if (!sourceMode) {
                source.value = area.innerHTML;
                area.hidden = true;
                source.hidden = false;
                source.focus();
            } else {
                area.innerHTML = source.value;
                source.hidden = true;
                area.hidden = false;
            }
            sourceMode = !sourceMode;
            btn.classList.toggle('is-active', sourceMode);
            toolbar.classList.toggle('is-source', sourceMode);
        }

        if (form) form.addEventListener('submit', sync);
    }

    function embedSrc(url) {
        var m = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
        if (m) return 'https://www.youtube-nocookie.com/embed/' + m[1];
        m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
        if (m) return 'https://player.vimeo.com/video/' + m[1];
        return '';
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ---------- Image preview for file inputs ---------- */

    document.querySelectorAll('[data-preview-input]').forEach(function (input) {
        var box = input.parentNode.querySelector('[data-preview-box]');
        if (!box) return;
        var img = box.querySelector('[data-preview-img]');
        input.addEventListener('change', function () {
            var file = input.files[0];
            if (!file) return;
            img.src = URL.createObjectURL(file);
            box.classList.remove('is-empty');
        });
    });

    /* ---------- Character counters ---------- */

    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        var field = document.getElementById(counter.dataset.counterFor);
        var max = parseInt(counter.dataset.max, 10);
        if (!field) return;
        function update() {
            var len = field.value.length;
            counter.textContent = len + ' / ' + max;
            counter.classList.toggle('over', len > max);
        }
        field.addEventListener('input', update);
        update();
    });

    /* ---------- Confirm before destructive actions ---------- */

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
})();
