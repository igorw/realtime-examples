var express = require('express'),
    app = express.createServer(),
    es = require('eventstream'),
    redis = require('redis-client').createClient(),
    streams = [];

redis.subscribeTo("*", 
  function (channel, message, subscriptionPattern) {
    var event = {
      event: channel,
      data: message
    };

    streams.forEach(function (eventStream, index) {
      if (!eventStream.isOpen()) {
        streams.splice(index, 1);
        return;
      }
      eventStream.sendMessage(event);
    });
  });

app.use(express.logger());

app.use(function(req, res, next) {
    
    // Check if this is an event stream request
    if (req.url === '/my-event-stream') {
    
        // Create an EventStream object and link it to the request/response
        var eventStream = new es.EventStream(req, res);

	eventStream.init();

        // Start sending keep-alive messages every 15 seconds
        (function keepAlive() {
            if (eventStream.isOpen()) {
                eventStream.keepAlive();
                setTimeout(keepAlive, 15000);
            }
        }());
        
	streams.push(eventStream);
	return;
    }

    next();
    
});

app.use(express.static(__dirname + '/public'));

app.listen(1234);
