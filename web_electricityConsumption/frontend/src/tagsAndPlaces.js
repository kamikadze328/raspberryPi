const tagGroups = [
    {
        name: 'P (kVt)',
        tags: [
            {
                id: 0,
                name: 'Актив'
            }, {
                id: 1,
                name: 'Реакт'
            }, {
                id: 2,
                name: 'Сумма'
            },
        ]
    },
    {
        name: 'U (V)',
        tags: [
            {
                id: 3,
                name: 'Фаза A'
            }, {
                id: 4,
                name: 'Фаза B'
            }, {
                id: 5,
                name: 'Фаза C'
            }, {
                id: 6,
                name: 'Сумма'
            },
        ]
    },
    {
        name: 'A (A)',
        tags: [
            {
                id: 7,
                name: 'Фаза A'
            }, {
                id: 8,
                name: 'Фаза B'
            }, {
                id: 9,
                name: 'Фаза C'
            }, {
                id: 10,
                name: 'Сумма'
            },
        ]
    },
]
const places = [
    {
        id: 0,
        name: 'ЦЕХ'
    },
    {
        id: 1,
        name: 'ЦЕХ Свет'
    },
    {
        id: 2,
        name: 'АБК'
    },
    {
        id: 3,
        name: 'УНП'
    }
]
const groupsAndPlaces = [
    //group 0
    {
        group: 0,
        place: 0,
        tag: 0
    }, {
        group: 0,
        place: 1,
        tag: 1
    }, {
        group: 0,
        place: 2,
        tag: 2
    }, {
        group: 0,
        place: 3,
        tag: 3
    },
    //group 1
    {
        group: 1,
        place: 0,
        tag: 4
    }, {
        group: 1,
        place: 1,
        tag: 5
    }, {
        group: 1,
        place: 2,
        tag: 6
    }, {
        group: 1,
        place: 3,
        tag: 7
    },
    //group 2
    {
        group: 2,
        place: 0,
        tag: 8
    }, {
        group: 2,
        place: 1,
        tag: 9
    }, {
        group: 2,
        place: 2,
        tag: 10
    }, {
        group: 2,
        place: 3,
        tag: 11
    },
    //group 3
    {
        group: 3,
        place: 0,
        tag: 12
    }, {
        group: 3,
        place: 1,
        tag: 13
    }, {
        group: 3,
        place: 2,
        tag: 14
    }, {
        group: 3,
        place: 3,
        tag: 15
    },
    //group 4
    {
        group: 4,
        place: 0,
        tag: 16
    }, {
        group: 4,
        place: 1,
        tag: 17
    }, {
        group: 4,
        place: 2,
        tag: 18
    }, {
        group: 4,
        place: 3,
        tag: 19
    },
    //group 5
    {
        group: 5,
        place: 0,
        tag: 20
    }, {
        group: 5,
        place: 1,
        tag: 21
    }, {
        group: 5,
        place: 2,
        tag: 22
    }, {
        group: 5,
        place: 3,
        tag: 23
    },
    //group 6
    {
        group: 6,
        place: 0,
        tag: 24
    }, {
        group: 6,
        place: 1,
        tag: 25
    }, {
        group: 6,
        place: 2,
        tag: 26
    }, {
        group: 6,
        place: 3,
        tag: 27
    },
    //group 7
    {
        group: 7,
        place: 0,
        tag: 28
    }, {
        group: 7,
        place: 1,
        tag: 29
    }, {
        group: 7,
        place: 2,
        tag: 30
    }, {
        group: 7,
        place: 3,
        tag: 31
    },
    //group 8
    {
        group: 8,
        place: 0,
        tag: 32
    }, {
        group: 8,
        place: 1,
        tag: 33
    }, {
        group: 8,
        place: 2,
        tag: 34
    }, {
        group: 8,
        place: 3,
        tag: 35
    },
    //group 9
    {
        group: 9,
        place: 0,
        tag: 36
    }, {
        group: 9,
        place: 1,
        tag: 37
    }, {
        group: 9,
        place: 2,
        tag: 38
    }, {
        group: 9,
        place: 3,
        tag: 39
    },
    //group 10
    {
        group: 10,
        place: 0,
        tag: 40
    }, {
        group: 10,
        place: 1,
        tag: 41
    }, {
        group: 10,
        place: 2,
        tag: 42
    }, {
        group: 10,
        place: 3,
        tag: 43
    },
]

export {
    tagGroups, places, groupsAndPlaces
}