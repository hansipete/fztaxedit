function getId($dom){

    var classes = $dom.attr('class'),
        matches = classes.match("-\\d+"), //matches every score followed by a number
        id;

    if(matches){
        id = matches[0];
        id = id.substr(1); // remove the prepending score
    }

    return id;
}