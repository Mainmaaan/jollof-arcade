<?phpRoute::get('/game/{name}', function ($name) {
    return "GAME PAGE WORKING: " . $name;
});