/**
 * RateLimit class
 * Based on classes/modules/core/RateLimit.class.php
 *
 * id: string to group similar calls
 * allowed_calls: integer that represents the number of calls in the time_frame
 * time_frame: integer number of seconds in which defined number of allowed_calls of specified id may be made
 *
 * Basic use case:
 *
 *    RateLimit.setID( 101 ); //unique identifier for this group of calls
 *    RateLimit.setAllowedCalls( 10 ); //max number of calls in time frame
 *    RateLimit.setTimeFrame( 900 ); //15 minutes in seconds
 *    if ( RateLimit.check() ) {
 *          //do rate limited activity
 *    } else {
 *          //do not do rate limited activity
 *    }
 *
 */
var RateLimit = function(){};

//attributes
RateLimit.memory = {};
RateLimit.id = '';
RateLimit.allowed_calls = 10;
RateLimit.time_frame = 3600; //1 hr

RateLimit.getID = function() {
	return this.id;
};
RateLimit.setID = function( value ) {
	if ( value != '' ) {
		this.id = value;
		return true;
	}
	return false;
};

RateLimit.getAllowedCalls = function() {
	return this.allowed_calls;
};
RateLimit.setAllowedCalls = function(value) {
	if ( value != '' ) {
		this.allowed_calls = value;
		return true;
	}
	return false;
};

RateLimit.getTimeFrame = function() {
	return this.time_frame;
};
RateLimit.setTimeFrame = function(value) {
	if ( value != '' ) {
		this.time_frame = value;
		return true;
	}
	return false;
};

RateLimit.getRateData = function() {
	if ( typeof(this.memory[this.id]) == "undefined" ) {
		return null;
	}
	return this.memory[this.id];
};
RateLimit.setRateData = function(value) {
	if ( typeof(this.memory[this.id]) == "undefined" ) {
		this.memory[this.id] ={};
	}
	if ( value != '' ) {
		this.memory[this.id] = value;
		return true;
	}
	return false;
};

RateLimit.getAttempts = function() {
	var rate_data = this.getRateData();
	if ( Global.isSet(rate_data['attempts']) ) {
		return rate_data['attempts'];
	}

	return false;
}

/**
 * @returns {boolean}
 */
RateLimit.check = function( ) {
	if ( this.getID() != '' ) {
		var rate_data = this.getRateData();
		var new_time = (new Date()).getTime();

		if ( Global.isSet(rate_data) == false ) {
			rate_data = {
				first_date: new_time,
				attempts: 0
			}
		} else if ( Global.isSet(rate_data) ) {

			var time_frame_milliseconds = this.getTimeFrame() * 1000;
			if ( rate_data.attempts > this.getAllowedCalls() && rate_data.first_date >= (new_time - time_frame_milliseconds) ) {
				Debug.Text('RateLimit limiting [' + rate_data.attempts +'/'+ this.getAllowedCalls() +'] in '+ (Math.floor((new_time - rate_data.first_date)/1000)) +'/'+ this.getTimeFrame() +'sec', 'RateLimit.js', 'RateLimit', 'check', 10);
				return false;
			} else if ( rate_data.first_date < new_time - time_frame_milliseconds ) {
				rate_data = {
					first_date: new_time,
					attempts: 0
				}
			}
		}

		rate_data.attempts++;
		this.setRateData(rate_data);
	}
	return true;
};

RateLimit.delete = function(id) {
	if( id != null ) {
		this.id = id;
	}
	delete this.memory[this.id];
};
