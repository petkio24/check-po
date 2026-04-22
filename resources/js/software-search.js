// resources/js/software-search.js

class SoftwareSearch {
    constructor() {
        this.searchInput = document.getElementById('software-search');
        this.resultsContainer = document.getElementById('search-results');
        this.init();
    }

    init() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', debounce(() => this.search(), 300));
        }
    }

    async search() {
        const query = this.searchInput.value.trim();

        if (query.length < 2) {
            this.hideResults();
            return;
        }

        try {
            const response = await fetch(`/allowed-software/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            this.displayResults(data);
        } catch (error) {
            console.error('Ошибка поиска:', error);
        }
    }

    displayResults(results) {
        if (!this.resultsContainer) return;

        if (results.length === 0) {
            this.resultsContainer.innerHTML = '<div class="p-4 text-gray-500">Ничего не найдено</div>';
            this.showResults();
            return;
        }

        const html = results.map(item => `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b" onclick="selectSoftware(${item.id})">
                <div class="font-medium">${item.name}</div>
                <div class="text-sm text-gray-500">Версия: ${item.version || '-'} | ${item.vendor || '-'}</div>
            </div>
        `).join('');

        this.resultsContainer.innerHTML = html;
        this.showResults();
    }

    showResults() {
        this.resultsContainer.classList.remove('hidden');
    }

    hideResults() {
        this.resultsContainer.classList.add('hidden');
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    new SoftwareSearch();
});
