function toggleProductEditMode(productId) {
    const viewRow = document.getElementById(`product-view-${productId}`);
    const editRow = document.getElementById(`product-edit-${productId}`);
    
    if (!viewRow || !editRow) return;

    const displayType = 'table-row'; 

    if (editRow.style.display === 'none' || editRow.style.display === '') {
        editRow.style.display = displayType;
        viewRow.style.display = 'none';
    } else {
        editRow.style.display = 'none';
        viewRow.style.display = displayType;
    }
}