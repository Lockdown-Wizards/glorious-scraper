let button = document.getElementById("eventExistenceTest");
let idArr = ["1561824204191735", "672743547252523", "72979354", "507385464294292"];

button.addEventListener("click", () => {
    checkEventsExistByIds(idArr).then((result) => {
        console.log(result);
    });
});

// Send an array of event ids to check whether or not the events exist.
// If an event exists, ties the id to the content for that event.
// Otherwise, tie the id to the boolean false.
async function checkEventsExistByIds(ids) {
    let fd = new FormData();
    fd.append("ids", JSON.stringify(ids));
    console.log(ids);
    // Default options are marked with *
    const response = await fetch("../wp-content/plugins/glorious-scraper/check-events-existence.php", {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        body: fd,
    });
    return response.json();
}
