/*
 * This script is responsible for calling the scraper, set-event, set-organization, and set-venue scripts syncronously.
 * While this process is syncronous, it kicks off asyncronously for each url.
 * NOTE: This script is run in the background without us needing to explicitly include the script in the glorious-scraper.php html.
 *       The reason why is due to the usage of the localize_urls function in the glorious-scraper.php script.
 */

window.addEventListener("DOMContentLoaded", () => {
    let scraperConsole = document.getElementById("scraperConsole");
    let scraperButton = document.getElementById("scraperButton");

    if (scraperButton !== null) {
        scraperButton.addEventListener("click", (e) => {
            //console.log(gloriousData);
            writeToConsole(
                "Now scraping for facebook events. This may take a while, so hang tight and make a cup of tea!"
            );
            // gloriousData (the URLs) was obtained from glorious-scraper.php using wp_localize_script.
            gloriousData.urls.forEach((urlData) => {
                // Scrape all events from the url
                let formData = new FormData();
                formData.append("url", urlData.url);
                postForm("../wp-content/plugins/glorious-scraper/scraper.php", formData).then((allEventArgs) => {
                    //console.log(allEventArgs);
                    // For each event, set the event in the events calendar
                    allEventArgs.forEach((eventArgs) => {
                        //console.log(eventArgs);
                        let eventFormData = new FormData();
                        eventFormData.append("args", JSON.stringify(eventArgs));
                        //console.log(JSON.stringify(eventArgs));
                        postForm("../wp-content/plugins/glorious-scraper/set-event.php", eventFormData).then(
                            (eventCreationId) => {
                                //console.log(eventCreationId);
                                writeToConsole(
                                    `(${eventArgs.Organizer}) Draft set for '${eventArgs.post_title}' with event id: ${eventCreationId}\n`
                                );
                            }
                        );
                    });
                });
                //console.log(url);
            });
        });
    }

    async function postForm(url = "", formData = {}) {
        // Default options are marked with *
        const response = await fetch(url, {
            method: "POST", // *GET, POST, PUT, DELETE, etc.
            body: formData, // body data type must match "Content-Type" header
        });
        return response.json(); // parses JSON response into native JavaScript objects
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
