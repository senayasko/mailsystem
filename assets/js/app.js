document.addEventListener('DOMContentLoaded', () => {
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
      const query = search.value.trim().toLocaleLowerCase('tr-TR');
      const items = [...document.querySelectorAll('[data-mail-item]')];
      let shown = 0;
      items.forEach((item) => {
        const visible = item.dataset.searchText.toLocaleLowerCase('tr-TR').includes(query);
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
    const selected = rowSelections.filter((input) => input.checked).length;
    rowSelections.forEach((input) => input.closest('.mail-row')?.classList.toggle('selected', input.checked));
    if (selectAll) {
      selectAll.checked = rowSelections.length > 0 && selected === rowSelections.length;
      selectAll.indeterminate = selected > 0 && selected < rowSelections.length;
    }
    if (selectionStatus) selectionStatus.textContent = selected > 0 ? `${selected} seçili` : `${rowSelections.length} mesaj`;
    if (bulkDelete) bulkDelete.disabled = selected === 0;
  };
  selectAll?.addEventListener('change', () => {
    rowSelections.forEach((input) => { input.checked = selectAll.checked; });
    updateSelection();
  });
  rowSelections.forEach((input) => input.addEventListener('change', updateSelection));
  bulkDeleteForm?.addEventListener('submit', () => {
    bulkDeleteForm.querySelectorAll('input[name="message_ids[]"]').forEach((input) => input.remove());
    rowSelections.filter((input) => input.checked).forEach((input) => {
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
      button.textContent = showing ? 'Göster' : 'Gizle';
    });
  });

  const textarea = document.querySelector('.compose-body textarea');
  const counter = document.querySelector('[data-char-count]');
  if (textarea && counter) {
    const update = () => counter.textContent = new Intl.NumberFormat('tr-TR').format(textarea.value.length);
    textarea.addEventListener('input', update);
    update();
  }

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
