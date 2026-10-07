document.addEventListener('DOMContentLoaded', () => {
  const language = document.documentElement.lang === 'en' ? 'en-US' : 'tr-TR';
  const strings = JSON.parse(document.getElementById('ui-translations')?.textContent || '{}');
  const translate = (key, count) => (strings[key] || key).replace('{count}', String(count ?? ''));
  const notice = document.querySelector('[data-async-notice]');
  const showError = (message) => {
    if (notice) { notice.textContent = message; notice.hidden = false; }
  };
  const updateUnread = (count) => {
    if (!Number.isInteger(count) || count < 0) return;
    document.querySelectorAll('[data-unread-count]').forEach((element) => {
      element.textContent = String(count);
      element.hidden = count === 0;
    });
    document.querySelectorAll('[data-unread-label]').forEach((element) => {
      element.textContent = translate('{count} okunmamış', count);
    });
  };
  const closeSidebar = () => document.body.classList.remove('sidebar-open');
  document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
  });
  document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', closeSidebar);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSidebar();
  });

  const search = document.querySelector('[data-mail-search]');
  if (search) {
    search.addEventListener('input', () => {
      const query = search.value.trim().toLocaleLowerCase(language);
      const items = [...document.querySelectorAll('[data-mail-item]')];
      let shown = 0;
      items.forEach((item) => {
        const visible = item.dataset.searchText.toLocaleLowerCase(language).includes(query);
        item.hidden = !visible;
        if (visible) shown += 1;
      });
      const empty = document.querySelector('[data-search-empty]');
      if (empty) empty.hidden = shown !== 0 || items.length === 0;
    });
  }

  const selectAll = document.querySelector('[data-select-all]');
  const rowSelections = [...document.querySelectorAll('[data-row-select]')];
  const selectionStatus = document.querySelector('[data-selection-status]');
  const bulkDelete = document.querySelector('[data-bulk-delete]');
  const bulkDeleteForm = document.querySelector('[data-bulk-delete-form]');
  const updateSelection = () => {
    const activeRows = rowSelections.filter((input) => input.isConnected);
    const selected = activeRows.filter((input) => input.checked).length;
    rowSelections.forEach((input) => input.closest('.mail-row')?.classList.toggle('selected', input.checked));
    if (selectAll) {
      selectAll.checked = activeRows.length > 0 && selected === activeRows.length;
      selectAll.indeterminate = selected > 0 && selected < activeRows.length;
    }
    if (selectionStatus) selectionStatus.textContent = translate(selected > 0 ? '{count} seçili' : '{count} mesaj', selected || activeRows.length);
    if (bulkDelete) bulkDelete.disabled = selected === 0;
  };
  selectAll?.addEventListener('change', () => {
    rowSelections.forEach((input) => { input.checked = selectAll.checked; });
    updateSelection();
  });
  rowSelections.forEach((input) => input.addEventListener('change', updateSelection));
  bulkDeleteForm?.addEventListener('submit', () => {
    bulkDeleteForm.querySelectorAll('input[name="message_ids[]"]').forEach((input) => input.remove());
    rowSelections.filter((input) => input.isConnected && input.checked).forEach((input) => {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'message_ids[]';
      hidden.value = input.dataset.messageId || input.value;
      bulkDeleteForm.appendChild(hidden);
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.closest('.password-field').querySelector('input');
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      button.textContent = translate(showing ? 'Göster' : 'Gizle');
      button.setAttribute('aria-label', translate(showing ? 'Şifreyi göster' : 'Şifreyi gizle'));
    });
  });

  const textarea = document.querySelector('.compose-body textarea');
  const counter = document.querySelector('[data-char-count]');
  if (textarea && counter) {
    const update = () => counter.textContent = new Intl.NumberFormat(language).format(textarea.value.length);
    textarea.addEventListener('input', update);
    update();
  }

  const attachmentInput = document.querySelector('input[name="attachment"]');
  attachmentInput?.addEventListener('change', () => {
    const file = attachmentInput.files[0];
    attachmentInput.setCustomValidity(file && file.size > 2 * 1024 * 1024 ? translate('Dosya en fazla 2 MB olabilir.') : '');
    if (!attachmentInput.checkValidity()) attachmentInput.reportValidity();
  });

  document.querySelectorAll('[data-star-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!window.fetch) return;
      event.preventDefault();
      const button = form.querySelector('button');
      if (button.disabled) return;
      button.disabled = true;
      if (notice) notice.hidden = true;
      try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || translate('İşlem tamamlanamadı. Tekrar dene.'));
        if (typeof data.is_starred !== 'boolean') throw new Error(translate('İşlem tamamlanamadı. Tekrar dene.'));
        button.classList.toggle('is-starred', data.is_starred);
        button.classList.toggle('starred', data.is_starred && button.classList.contains('icon-button'));
        button.setAttribute('aria-pressed', String(data.is_starred));
        button.setAttribute('aria-label', translate(data.is_starred ? 'Yıldızı kaldır' : 'Yıldızla'));
        updateUnread(data.unread_count);
        const list = form.closest('[data-mail-list]');
        if (list?.dataset.box === 'starred' && !data.is_starred) {
          form.closest('.mail-row').remove();
          updateSelection();
          const empty = list.querySelector('[data-search-empty]');
          if (!list.querySelector('[data-mail-item]')) {
            empty.textContent = translate('Yıldızlı mesaj yok');
            empty.hidden = false;
          } else search?.dispatchEvent(new Event('input'));
        }
      } catch (error) {
        showError(error instanceof SyntaxError || error instanceof TypeError ? translate('İşlem tamamlanamadı. Tekrar dene.') : error.message);
      } finally {
        button.disabled = false;
      }
    });
  });

  // Yeni iletilerin sayısını ve başka sekmede okunan mesajları JSON ile eşitle.
  const statusUrl = document.body.dataset.statusUrl;
  let statusBusy = false;
  let statusTimer;
  const refreshUnread = async () => {
    if (!statusUrl || statusBusy || document.hidden) return;
    statusBusy = true;
    try {
      const response = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
      if (response.status === 401) { clearInterval(statusTimer); return; }
      if (response.ok) updateUnread((await response.json()).unread_count);
    } catch (_) {
      // Geçici bağlantı sorununda son sayıyı koru; sonraki kontrol tekrar dener.
    } finally { statusBusy = false; }
  };
  if (statusUrl && window.fetch) {
    refreshUnread();
    statusTimer = setInterval(refreshUnread, 30000);
    document.addEventListener('visibilitychange', refreshUnread);
  }

  // Dil değiştirirken yazılmış metni koru; dosya seçimi tarayıcıda yeniden yapılır.
  document.querySelectorAll('.language-switch').forEach((form) => {
    form.addEventListener('submit', () => {
      const compose = document.querySelector('.compose-form');
      if (!compose) return;
      form.querySelectorAll('[data-compose-language]').forEach((input) => input.remove());
      for (const name of ['recipient', 'subject', 'body']) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'compose_' + name;
        input.value = compose.elements[name].value;
        input.dataset.composeLanguage = '';
        form.appendChild(input);
      }
    });
  });

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      const submit = form.querySelector('.primary-button[type="submit"]');
      if (submit && form.checkValidity()) {
        submit.classList.add('loading');
        submit.disabled = true;
      }
    });
  });
});
