$( document ).ready(function() {
        
        var markers = new L.FeatureGroup();
        var map;
        
        $("#exposeModal").hide();

        $(function(){
            var criteriaItem = $('.ui-state-default'),
                unorderedList = $('#sortable');
            
            var count = 0;
            unorderedList.sortable({
                placeholder: "ui-state-placeholder",
                helper: 'clone', 
                update: function (e, ui){
                    var $lis = $(this).children('li');
                    $lis.each(function() {
                        var $li = $(this);
                        var newVal = $(this).index() + 1;
                        $(this).children('.indexNum').html('<p>'+newVal+'. </p>');
                        count++;
                        if(count == 9){
                            count = 0;
                            getBoundsAndSendAjax();
                        }
                    });
                }
            });
            
            //insert list item index into html
            var $lis = unorderedList.children('li');
            $.each($('li div'), function(index, value){
                var numVal = $(this).html('<p>'+(index+1)+'. </p>');
            });
            
            unorderedList.disableSelection();    
            
            criteriaItem.mouseenter(function(){
                   $(this).css('box-shadow', '0 0 1em grey');
            })
            criteriaItem.mouseleave(function(){
                    $(this).css('box-shadow', 'none')
            })

            getBoundsAndSendAjax();
        })
        
        map = L.map('map').setView([52.52,13.384], 12); //Grundkartenelement erzeugen
        // colored map
        L.tileLayer('http://a.tiles.mapbox.com/v3/examples.map-i87786ca/{z}/{x}/{y}.png').addTo(map);
        // grayscale map
        // L.tileLayer('http://a.tiles.mapbox.com/v3/examples.map-20v6611k/{z}/{x}/{y}.png').addTo(map);
        /*var renate = L.marker([52.49744, 13.46531]).addTo(map);
        renate.bindPopup("<b>Zur wilde Renate</b><br><a href='http://www.cluelist.com/de-de/berlin-salon_zur_wilden_renate'>Go to article about Zur Wilden Renate</a>.").openPopup();

        var sisyphos = L.marker([52.493197, 13.491805]).addTo(map);
        sisyphos.bindPopup("<b>Sisyphos</b><br><a href='http://www.tagesspiegel.de/berlin/streifzug-durch-die-clubs-von-berlin-jetzt-steigt-die-party-in-lichtenberg/10119176.html'>Go to article about Sisyphos</a>");

        var klunkerkranich = L.marker([52.482158, 13.432996]).addTo(map);
        klunkerkranich.bindPopup("<b>Klunkerkranich</b><br><a href='http://www.findingberlin.com/klunkerkranich-rooftop/'>Go to article about Klunkerkranich</a>");

        var czarHagestolz = L.marker([52.51782, 13.52555]).addTo(map);
        czarHagestolz.bindPopup("<b>Czar Hagestolz</b><br><a href='http://www.berliner-zeitung.de/nachtleben/gelaende-alte-boerse-czar-hagestolz-eroeffnet-in-marzahn,21528120,28265784.html'>Go to article about Czar Hagestolz</a>");

        var elseClub = L.marker([52.495087, 13.462532]).addTo(map);
        elseClub.bindPopup("<b>Else</b><br><a href='http://musikbord.de/wilde-renate-neuer-club-open-air-ableger-else-14666'>Go to article about Else</a>");

        var incognito = L.marker([52.494178, 13.344738]).addTo(map);
        incognito.bindPopup("<b>Incognito</b><br><a href='http://darialena.jimdo.com/events-locations/locations-in-berlin/incognito/'>Go to article about Incognito</a>");

        var clandestine = L.marker([52.453102, 13.320439]).addTo(map);
        clandestine.bindPopup("<b>Clandestine</b><br><a href='http://mixology.eu/bars/clandestine-bar/'>Go to article about Clandestine</a>");

        var m = L.marker([52.453102, 13.320439], {draggable:true}).bindLabel('A sweet static label!', { noHide: true })
        .addTo(map)
        .showLabel();*/

    // standalone popup
    //var popup = L.popup()
    //.setLatLng([52.49744, 13.46531])
    //.setContent("Zur wilde Renate")
    //.openOn(map);

    function getNumber(text){
        return text.substr(0, text.length -2);
    }

    function updateMap(data){

        var flats = JSON.parse(data);
        
        var flatIcon = L.icon({
            iconUrl: 'images/party_marker.svg',
            iconSize: [32, 37],
            iconAnchor: [16, 37],
            popupAnchor: [0, -28]
        });
        for(var i=0; i < flats.length; i++){
            var renate = L.marker([flats[i].geometry.lat, flats[i].geometry.lon],{icon: flatIcon});
            markers.addLayer(renate);
        }
        console.log("sadas");

        map.addLayer(markers);
    }

    function getBoundsAndSendAjax(){

        markers.clearLayers();

        var barsV = $("#bars p").text();
        var clubsV = $("#clubs p").text();
        var liquorV = $("#liquor p").text();
        var balconyV = $("#balcony p").text();
        var second_toiletV = $("#second_toilet p").text();
        var imbissesV = $("#imbisses p").text();
        var supermarketsV = $("#supermarkets p").text();
        var transportV = $("#transport p").text();
        var atmsV = $("#atms p").text();
        var priceV = $("#price p").text();

        barsV = getNumber(barsV);
        clubsV = getNumber(clubsV);
        liquorV = getNumber(liquorV);
        balconyV = getNumber(balconyV);
        second_toiletV = getNumber(second_toiletV);
        imbissesV = getNumber(imbissesV);
        supermarketsV = getNumber(supermarketsV);
        transportV = getNumber(transportV);
        atmsV = getNumber(atmsV);
        priceV = getNumber(priceV);

        var bounds = map.getBounds();
        
        var eastV = bounds.getEast();
        var westV = bounds.getWest();
        var northV = bounds.getNorth();
        var southV = bounds.getSouth();

        $.ajax({ url: '../php/main.php',
             data: {action: 'getAppartments', east: eastV, west: westV, north: northV, south: southV,
                bars:barsV,clubs:clubsV,liquor:liquorV,balcony:balconyV,second_toilet:second_toiletV,
                imbisses:imbissesV,supermarkets:supermarketsV,transport:transportV,atms:atmsV,price:priceV },
             type: 'get',
             success: function(output) {
                //alert(output);
                updateMap(output);
             }
        });
    }

    //Calculate Boundary Coordinates After Zoom
    map.on('zoomend', function(event){
        getBoundsAndSendAjax();
    });

    map.on('moveend', function(event){
        getBoundsAndSendAjax();
    });

    $("#startSearch").click(function() {
        $("#exposeModal").toggle();
    });
});
