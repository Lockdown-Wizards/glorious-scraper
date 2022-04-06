let cronJobTestButton = document.getElementById("cronjobTest");

cronJobTestButton.addEventListener("click", () => {
    cronjobTest().then((result) => {
        console.log(result);
    });
});

// Send an array of event ids to check whether or not the events exist.
// If an event exists, ties the id to the content for that event.
// Otherwise, tie the id to the boolean false.
async function cronjobTest() {
    // Default options are marked with *
    const response = await fetch("../wp-content/plugins/glorious-scraper/cron-job.php", {
        method: "GET", // *GET, POST, PUT, DELETE, etc.
    });
    return response.json();
}
