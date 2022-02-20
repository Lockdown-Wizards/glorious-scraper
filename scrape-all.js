window.addEventListener("DOMContentLoaded", () => {
    let scraperConsole = document.getElementById("scraperConsole");
    let scraperButton = document.getElementById("scraperButton");

    if (scraperButton !== null) {
        scraperButton.addEventListener("click", (e) => {
            console.log(gloriousData);
            // scraperUrls was obtained from glorious-scraper.php using wp_localize_script.
            gloriousData.urls.forEach((url) => {
                // Scrape all events from the url
                /*post("../wp-content/plugins/glorious-scraper/add-url.php", { url: url }).then((data) => {
                    console.log(data);
                });*/
                console.log(url);
            });
        });
    } else {
        console.log("scraperButton not found. you're likely not on the Event Scraper admin page.");
    }

    async function post(url = "", data = {}) {
        // Default options are marked with *
        const response = await fetch(url, {
            method: "POST", // *GET, POST, PUT, DELETE, etc.
            headers: {
                "Content-Type": "application/json",
                // 'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: JSON.stringify(data), // body data type must match "Content-Type" header
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
