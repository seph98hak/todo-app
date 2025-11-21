// assets/main.js - dark mode, edit modal population, export CSV, delete modal
document.addEventListener('DOMContentLoaded', function () {
  // Dark mode toggle
  const toggle = document.getElementById('darkModeToggle');
  if (toggle) {
    const body = document.body;
    const saved = localStorage.getItem('todo_dark_mode');
    if (saved === '1') body.classList.add('dark-mode');
    toggle.addEventListener('click', function () {
      body.classList.toggle('dark-mode');
      localStorage.setItem('todo_dark_mode', body.classList.contains('dark-mode') ? '1' : '0');
    });
  }

  // Edit modal population remains in-page (todos.php includes a JS function)
});

// Utility: Export table rows to CSV (called from todos.php)
function exportTodosCSV() {
  const rows = [['Title','Status','Priority','Due Date','Category']];
  document.querySelectorAll('#todosTable tbody tr').forEach(tr => {
    const cols = tr.querySelectorAll('td');
    const row = [
      cols[0].innerText.trim(),
      cols[1].innerText.trim(),
      cols[2].innerText.trim(),
      cols[3].innerText.trim(),
      cols[4] ? cols[4].innerText.trim() : ''
    ];
    rows.push(row);
  });
  const csvContent = rows.map(r => r.map(v => `"${v.replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'todos_export.csv';
  document.body.appendChild(a);
  a.click();
  a.remove();
}
