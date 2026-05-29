const tbody = document.querySelector('#visits-body');
const statusElement = document.querySelector('#status');
const refreshButton = document.querySelector('#refresh-button');

async function loadVisitsPerHour() {
  statusElement.textContent = 'Loading...';

  try {
    const response = await fetch('/api/analytics/visits-per-hour');

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    const rows = await response.json();

    if (rows.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2">No visits registered yet.</td></tr>';
    } else {
      tbody.innerHTML = rows
        .map((row) => `<tr><td>${row.hour}</td><td>${row.visits}</td></tr>`)
        .join('');
    }

    statusElement.textContent = `Last updated at ${new Date().toLocaleTimeString()}`;
  } catch (error) {
    tbody.innerHTML = '<tr><td colspan="2">Could not load analytics.</td></tr>';
    statusElement.textContent = error.message;
  }
}

refreshButton.addEventListener('click', loadVisitsPerHour);
loadVisitsPerHour();
