document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('.ca-product-table');
    if (!table) return;

    table.addEventListener('click', (event) => {
        const button = event.target.closest('.ca-gallery-button');
        if (!button) return;

        const productRow = button.closest('.ca-product-row');
        if (!productRow) return;

        const existingGallery = productRow.nextElementSibling;
        if (existingGallery?.classList.contains('ca-gallery-row')) {
            existingGallery.remove();
            button.classList.remove('ca-gallery-button--active');
            return;
        }

        let images = [];
        try {
            images = JSON.parse(productRow.dataset.images || '[]');
        } catch (e) {
            return;
        }

        if (!images.length) return;

        const displayImages = images.slice(0, 3);
        const columnCount = productRow.children.length;

        const galleryRow = document.createElement('tr');
        galleryRow.classList.add('ca-gallery-row');

        const cell = document.createElement('td');
        cell.setAttribute('colspan', columnCount);

        const container = document.createElement('div');
        container.classList.add('ca-gallery-container');

        displayImages.forEach((src) => {
            const img = document.createElement('img');
            img.src = src;
            img.alt = 'Product image';
            img.classList.add('ca-gallery-image');
            container.appendChild(img);
        });

        cell.appendChild(container);
        galleryRow.appendChild(cell);
        productRow.after(galleryRow);
        button.classList.add('ca-gallery-button--active');
    });
});