window.addEventListener("DOMContentLoaded", () => {
    let exportButtonElem = document.getElementById("exportButton");
    let exportingMessageElem = document.getElementById("exportingMessage");
    let exportingCompleteMessageElem = document.getElementById("exportCompleteMessage");
    let exportingErrorMessageElem = document.getElementById("exportErrorMessage");

    exportButtonElem.addEventListener("click", () => {
        exportButtonElem.disabled = true;
        exportingMessageElem.className = ""; // unhide the 'export processing' message
        exportingCompleteMessageElem.className = "hidden";
        exportingErrorMessageElem.className = "hidden";

        post("../wp-content/plugins/glorious-scraper/export.php")
            .then((res) => {
                console.log(res);
                exportingMessageElem.className = "hidden";
                exportingCompleteMessageElem.className = "";
                exportingErrorMessageElem.className = "hidden";
                exportButtonElem.disabled = false;
            })
            .catch((err) => {
                console.log(err);
                exportingErrorMessageElem.innerText = "An error with the export has occurred: " + err;
                exportButtonElem.disabled = false;
                exportingMessageElem.className = "hidden";
                exportingCompleteMessageElem.className = "hidden";
                exportingErrorMessageElem.className = "";
            });
    });

    async function post(url = "", formData = {}) {
        // Default options are marked with *
        const response = await fetch(url, {
            method: "POST", // *GET, POST, PUT, DELETE, etc.
            body: formData, // body data type must match "Content-Type" header
        });
        return response.json(); // parses JSON response into native JavaScript objects
    }

    // Function which writes to the console shown on the Event Scraper admin page.
    function writeToConsole(msg) {
        let messageElem = document.createElement("div");
        messageElem.className = "scraper-console-line";
        messageElem.innerHTML = msg;
        scraperConsole.append(messageElem); // scraperConsole is a global
    }
});
