/* GameHub 前端交互 */
(function () {
  'use strict';

  // 删除等危险操作的二次确认
  document.querySelectorAll('.js-confirm').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      if (!window.confirm(form.getAttribute('data-confirm') || '确定执行该操作？')) {
        ev.preventDefault();
      }
    });
  });

  // 管理端表单：选图即时预览
  document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var img = document.querySelector(input.getAttribute('data-preview'));
      if (!img || !input.files || !input.files[0]) return;
      img.src = URL.createObjectURL(input.files[0]);
      img.hidden = false;
    });
  });

  // 复制到剪贴板（详情页 AppID）
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var el = document.querySelector(btn.getAttribute('data-copy'));
      if (!el) return;
      var text = el.textContent.trim();
      var done = function () { btn.textContent = '已复制'; setTimeout(function () { btn.textContent = '复制'; }, 1500); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done);
      } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        done();
      }
    });
  });
})();
