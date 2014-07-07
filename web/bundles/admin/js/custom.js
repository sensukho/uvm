window.onload = function () {
    if (typeof history.pushState === "function") {
        history.pushState("jibberish", null, null);
        window.onpopstate = function () {
            history.pushState('newjibberish', null, null);
            // console.log('oK! 1');
            // console.log( typeof history.pushState );
        };
    }
    else {
        var ignoreHashChange = true;
        window.onhashchange = function () {
            if (!ignoreHashChange) {
                ignoreHashChange = true;
                window.location.hash = Math.random();
                // console.log('oK! 2');
            }
            else {
                ignoreHashChange = false;
                // console.log('oK! 3');
            }
        };
    }

}