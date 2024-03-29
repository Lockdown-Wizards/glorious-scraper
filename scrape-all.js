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
        scraperButton.addEventListener("click", () => {
            scraperConsole.style.height = "360px";
            scraperConsole.style.padding = "10px";
            scraperConsole.style.border = "2px solid black";
            scraperButton.disabled = true; // Prevent the 'Run Scraper' button from getting spammed.
            writeToConsole(
                "Now scraping for facebook events. This may take a while, so hang tight and make a cup of tea!"
            );

            // gloriousData (the URLs) was obtained from glorious-scraper.php using wp_localize_script.
            let completed = 0; // Keeps track of how many URLs have been completed.
            let totalEvents = 0; // Keeps track of how many events are being scraped.
            gloriousData.urls.forEach((urlData, urlIndex) => {
                writeToConsole(
                    `Attempting to scrape event data from <a href='${urlData.url}'>'${urlData.url}'</a>...</span>\n`
                );

                // Scrape all events from the url
                let formData = new FormData();
                formData.append("url", urlData.url);
                postForm("../wp-content/plugins/glorious-scraper/scraper.php", formData).then((allArgs) => {
                    // Allows the scraper to keep track of how many events there are left.
                    totalEvents += allArgs.length;
                    console.log(allArgs);
                    if (!allArgs) {
                        writeToConsole(
                            `<span style="color: red;">(Error) Proxy service ran into an error gathering data from <a href='${urlData.url}'>'${urlData.url}'</a>. Repeat this scrape and cross your fingers.</span>\n`
                        );
                        return;
                    }

                    // For each event, set the venue and then the event in the events calendar
                    // We create the venue first so that we may add it to the event.
                    allArgs.forEach((args) => {
                        if (args.event.Location === "" || args.event.Location.includes("http")) {
                            // Create the event
                            let eventFormData = new FormData();
                            eventFormData.append("args", JSON.stringify(args.event));
                            postForm("../wp-content/plugins/glorious-scraper/set-event.php", eventFormData).then(
                                (eventCreationId) => {
                                    writeToConsole(
                                        `(${args.event.Organizer}) Event set for '${args.event.post_title}' with event id: ${eventCreationId}\n`
                                    );
                                    writeToConsole(
                                        `<span style="color: red;">(Error) Unable to set the venue for event <a href='${args.event.EventURL}'>'${args.event.post_title}'</a>. Please enter this manually.</span>\n`
                                    );

                                    completed++;
                                    if (completed === totalEvents && urlIndex + 1 === gloriousData.urls.length) {
                                        // Display to console when scraping is complete.
                                        writeToConsole("Scraping complete.");
                                        scraperButton.disabled = false;
                                    }
                                }
                            );
                        } else {
                            let venueFormData = new FormData();
                            venueFormData.append("args", JSON.stringify(args.venue));
                            postForm("../wp-content/plugins/glorious-scraper/set-venue.php", venueFormData).then(
                                (venueCreationId) => {
                                    writeToConsole(
                                        `(${args.venue.City}, ${args.venue.State}) Venue '${args.venue.Venue}' set with venue id: ${venueCreationId}\n`
                                    );

                                    // This is supposed to set the venue of the event, but doesn't work right now.
                                    args.event.Venue = JSON.stringify(args.venue);

                                    // Create the event
                                    let eventFormData = new FormData();
                                    eventFormData.append("args", JSON.stringify(args.event));
                                    postForm(
                                        "../wp-content/plugins/glorious-scraper/set-event.php",
                                        eventFormData
                                    ).then((eventCreationId) => {
                                        writeToConsole(
                                            `(${args.event.Organizer}) Event set for '${args.event.post_title}' with event id: ${eventCreationId}\n`
                                        );

                                        // Link the event to the venue
                                        let linkVenueToEventFormData = new FormData();
                                        linkVenueToEventFormData.append("venueId", JSON.stringify(venueCreationId));
                                        linkVenueToEventFormData.append("eventId", JSON.stringify(eventCreationId));
                                        postForm(
                                            "../wp-content/plugins/glorious-scraper/pair-venue-to-event.php",
                                            linkVenueToEventFormData
                                        ).then(() => {
                                            completed++;
                                            if (
                                                completed === totalEvents &&
                                                urlIndex + 1 === gloriousData.urls.length
                                            ) {
                                                // Display to console when scraping is complete.
                                                writeToConsole("Scraping complete.");
                                                scraperButton.disabled = false;
                                            }
                                        });
                                    });
                                }
                            );
                        }
                    });

                    if (allArgs.length <= 0) {
                        writeToConsole(
                            `No upcoming events for <a href="${urlData.url}">${urlData.url}</a>. Skipping this url.`
                        );
                    }
                });
            });

            // In case no events are found, this provides a way to re-enable the 'Run Scraper' button.
            window.setTimeout(() => {
                if (completed === totalEvents) scraperButton.disabled = false;
            }, 20000 * gloriousData.urls.length);
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

    // Function which writes to the console shown on the Event Scraper admin page.
    function writeToConsole(msg) {
        let messageElem = document.createElement("div");
        messageElem.className = "scraper-console-line";
        messageElem.innerHTML = msg;
        scraperConsole.append(messageElem); // scraperConsole is a global
    }
});
