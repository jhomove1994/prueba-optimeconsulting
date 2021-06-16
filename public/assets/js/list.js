document.addEventListener("DOMContentLoaded", function(event) {
    const elementCategory = document.getElementById('delete-element-category');
    const elementProduct = document.getElementById('delete-element-category');
    
    if(elementCategory)
        elementCategory.addEventListener("click",deleteElement);
    if(elementProduct)
        elementProduct.addEventListener("click",deleteElement);
    
    const deleteElement = e => {
        e.preventDefault();
        console.log(e)
        const id = e.dataset.id;
        const dataResource = e.dataset.dataResource;
        result = window.confirm('Esta seguro que desea eliminar el elemento?');
        if(result)
            window.location.href = `/${dataResource}/${id}/delete`;
    }
});