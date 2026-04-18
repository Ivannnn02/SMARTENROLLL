const requirementsSearch = document.getElementById('requirementsSearch');
const requirementsFilter = document.getElementById('requirementsFilter');

if (requirementsSearch) {
  requirementsSearch.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      requirementsSearch.form?.submit();
    }
  });
}

if (requirementsFilter) {
  requirementsFilter.addEventListener('change', () => {
    requirementsFilter.form?.submit();
  });
}
