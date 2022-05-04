<?php
// Access the plugin config
$configs = include('config.php');
?>
<script>
//window.addEventListener("DOMContentLoaded", () => {
    let exportsList = document.getElementById("exportsList");
    let exportCurrentPage = 0;
    let exportPageSize = parseInt(<?php echo $configs["exportListPageSize"]; ?>)
    let totalExportPages = Math.floor(exportsList.childElementCount / exportPageSize);

    let exportPrevPage = document.getElementById("exportPrevPage");
    let exportNextPage = document.getElementById("exportNextPage");

    exportPrevPage.addEventListener("click", prevPage);
    exportNextPage.addEventListener("click", nextPage);
    renderList();

    // Global: currentExportPage
    function nextPage() {
        if (exportCurrentPage < totalExportPages) {
            exportCurrentPage++;
        }
        renderList();
    }

    // Global: currentExportPage
    function prevPage() {
        if (exportCurrentPage > 0) {
            exportCurrentPage--;
        }
        renderList();
    }

    // Re-renders the list. Useful for when the user changes the page they're looking at.
    // Globals: currentExportPage, exportPrevPage, exportNextPage
    function renderList() {
        // Show/hide options depending on which page the user is currently on.
        const listArray = Array.from(exportsList.children);
        listArray.forEach((elem, index) => {
            if (parseInt(elem.getAttribute("page")) === exportCurrentPage) {
                elem.classList.remove("hidden");
            } else {
                elem.classList.add("hidden");
            }
        });

        // Hide & show prev/next buttons depending on which page the user is on
        if (exportCurrentPage === 0) {
            exportPrevPage.disabled = true;
        } else {
            exportPrevPage.disabled = false;
        }
        if (totalExportPages === exportCurrentPage) {
            exportNextPage.disabled = true;
        } else {
            exportNextPage.disabled = false;
        }
    }
//});
</script>
