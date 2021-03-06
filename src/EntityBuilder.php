<?php

namespace Raisaev\UzTicketsParser;

use Raisaev\UzTicketsParser\Entity\Station;
use Raisaev\UzTicketsParser\Entity\Train;

class EntityBuilder
{
    //###################################

    /**
     * {
     *    title: "Днепр-Лоцманская",
     *    region: null,
     *    value: 2210701
     * }
     * @param array $parsedData
     * @return Station
     */
    public function constructStation(array $parsedData)
    {
        $station = new Station(
            $parsedData['title'],
            $parsedData['value']
        );
        return $station;
    }

    // ---------------------------------------

    /**
     {
        "num": "004П",
        "category": 0,
        "isTransformer": 0,
        "travelTime": "13:00",
        "from": {
            "code": "2210700",
            "station": "Днепр-Главный",
            "stationTrain": "Запорожье 1",
            "date": "понедельник, 24.12.2018",
            "time": "16:40",
            "sortTime": 1545662400,
            "srcDate": "2018-12-24"
        },
        "to": {
            "code": "2218000",
            "station": "Львов",
            "stationTrain": "Ужгород",
            "date": "вторник, 25.12.2018",
            "time": "05:40",
            "sortTime": 1545709200
        },
        "types": [{
            "id": "К",
            "title": "Купе",
            "letter": "К",
            "places": 89
        }],
        "child": {
            "minDate": "2004-12-25",
            "maxDate": "2018-12-21"
        },
        "allowStudent": 1,
        "allowBooking": 1,
        "isCis": 0,
        "isEurope": 0,
        "allowPrivilege": 0
    }
     * @param array $parsedData
     * @return Train
     */
    public function constructTrain(array $parsedData)
    {
        $fromStation = $this->constructStation([
            'title' => $parsedData['from']['station'],
            'value' => $parsedData['from']['code']
        ]);

        $formationFromStation = $this->constructStation([
            'title' => $parsedData['from']['stationTrain'],
            'value' => NULL
        ]);

        $toStation = $this->constructStation([
            'title' => $parsedData['to']['station'],
            'value' => $parsedData['to']['code']
        ]);

        $formationToStation = $this->constructStation([
            'title' => $parsedData['to']['stationTrain'],
            'value' => NULL
        ]);

        $seats = [];
        foreach ($parsedData['types'] as $seatData) {
            $seats[] = $this->constructSeat($seatData);
        }

        $fromDate = trim(explode(',', $parsedData['from']['date'])[1]) .' '. $parsedData['from']['time'];
        $toDate   = trim(explode(',', $parsedData['to']['date'])[1])   .' '. $parsedData['to']['time'];

        $train = new Train(
            $parsedData['num'],
            $formationFromStation,
            $fromStation,
            new \DateTime($fromDate, new \DateTimeZone('Europe/Kiev')),
            $formationToStation,
            $toStation,
            new \DateTime($toDate, new \DateTimeZone('Europe/Kiev')),
            $seats
        );

        return $train;
    }

    /**
    {
        "id": "К",
        "title": "Купе",
        "letter": "К",
        "places": 89
    }
     * @param array $parsedData
     * @return Train\Seat
     */
    public function constructSeat(array $parsedData)
    {
        $seat = new Train\Seat(
            $parsedData['letter'],
            $parsedData['title'],
            $parsedData['places']
        );
        return $seat;
    }

    /**
    {
    "num": 14,
        "type_id": "П",
        "type": "П",
        "class": "Г",
        "railway": 45,
        "free": 1,
        "byWishes": false,
        "hasBedding": true,
        "obligatoryBedding": false,
        "services": ["М",
        "Н"],
        "prices": {
            "Б": 15140
        },
        "reservePrice": 1700,
        "allowBonus": false,
        "air": null
    }
     * @param string $trainNumber
     * @param array $parsedData
     * @return Train\Coach
     */
    public function constructCoach($trainNumber, array $parsedData)
    {
        $coach = new Train\Coach(
            $trainNumber,
            $parsedData['num'],
            $parsedData['type'],
            $parsedData['class'],
            round(array_shift($parsedData['prices']) / 100, 2),
            $parsedData['free']
        );
        return $coach;
    }

    //###################################
}