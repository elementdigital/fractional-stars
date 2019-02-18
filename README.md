# fractional-stars
**PHP and AJAX star rating system**

* Cumulative average rating
* Dispays fractional stars
* Multiple objects on a single page
* Works in No JS environment

## **Parameters**
* itemid: (string) (required) unique id for the rating object
* itemtype: (string) additional identifier
* units: (int) how many stars to show
* unitwidth: (int) square size of star images
* multivote: (bool) allow users to rate multiple times per session
* rounding: (bool) rounds average to whole units 

## **Usage**
default params: display a 5 star rating object 

`echo $rating->setRatingObject("ID-1")`

custom params: display a 7 star rating object

`echo $rating->setRatingObject("ID-2", "demo", 7, 30, true, false)`
