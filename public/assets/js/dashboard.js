function toggleProductEditMode(productId) {
    const viewRow = document.getElementById(`product-view-${productId}`);
    const editRow = document.getElementById(`product-edit-${productId}`);
    
    if (!viewRow || !editRow) return;

    if (editRow.style.display === 'none') {
        editRow.style.display = 'block';
        viewRow.style.display = 'none';
    } else {
        editRow.style.display = 'none';
        viewRow.style.display = 'grid';
    }
}