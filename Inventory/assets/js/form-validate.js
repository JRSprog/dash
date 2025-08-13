document.addEventListener('DOMContentLoaded', function() {
    JsBarcode(".barcode").init();
});

document.querySelectorAll('.delete-product').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        const row = this.closest('tr'); // Get the table row
        
        if (confirm('Are you sure you want to delete this product?')) {
            fetch(`delete.php?id=${productId}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    row.style.opacity = 0;
                    setTimeout(() => {
                        row.remove();
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success';
                        alertDiv.textContent = data.message;
                        document.querySelector('.card-body').prepend(alertDiv);
                        // Remove the message after 3 seconds
                        setTimeout(() => alertDiv.remove(), 3000);
                    }, 300);
                } else {
                    alert('Error: ' + data.message);
                }
            })
        }
    });
});



//search engine
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('find');
    const productTable = document.getElementById('search');
    const rows = productTable.querySelectorAll('tbody tr');
    
    // Debounce function to limit how often search executes
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
    
    // Highlight matching text
    function highlightText(element, searchTerm) {
        const text = element.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        element.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
    }
    
    // Remove all highlights
    function removeHighlights() {
        document.querySelectorAll('.highlight').forEach(el => {
            const parent = el.parentNode;
            parent.textContent = parent.textContent;
        });
    }
    
    const performSearch = debounce(function() {
        const searchTerm = this.value.trim().toLowerCase();
        removeHighlights();
        
        if (searchTerm === '') {
            rows.forEach(row => row.style.display = '');
            return;
        }
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let rowMatches = false;
            
            // Skip the last cell (actions column)
            for (let i = 0; i < cells.length - 1; i++) {
                const cellText = cells[i].textContent.toLowerCase();
                if (cellText.includes(searchTerm)) {
                    rowMatches = true;
                    highlightText(cells[i], searchTerm);
                }
            }
            
            row.style.display = rowMatches ? '' : 'none';
        });
    }, 300);
    
    searchInput.addEventListener('input', performSearch);
});
