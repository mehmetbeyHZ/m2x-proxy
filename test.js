var http = require('https');
var urls = ['https://jsonplaceholder.typicode.com/todos/1', 'https://jsonplaceholder.typicode.com/todos/2'];
var completed_requests = 0;

urls.forEach(function(url) {
    var responses = [];
    http.get(url, function(res) {
        res.on('data', function(chunk){
            responses.push(chunk);
        });

        res.on('end', function(){
            if (completed_requests++ == urls.length - 1) {
                // All downloads are completed
                console.log('body:', responses.join());
            }
        });
    });
})