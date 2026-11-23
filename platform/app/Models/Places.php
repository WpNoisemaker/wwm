<?php
declare(strict_types=1);

/**
 * Where cities are, so "weddings near you" can work from the visitor's own location without any
 * outside map service: the cities African weddings happen in at home and across the diaspora.
 * name => [country, latitude, longitude].
 */
final class Places
{
    public const CITIES = [
        // Nigeria
        'Lagos' => ['Nigeria', 6.5244, 3.3792], 'Lekki' => ['Nigeria', 6.4698, 3.5852], 'Ikeja' => ['Nigeria', 6.6018, 3.3515],
        'Abuja' => ['Nigeria', 9.0765, 7.3986], 'Kano' => ['Nigeria', 12.0022, 8.5920], 'Ibadan' => ['Nigeria', 7.3775, 3.9470],
        'Port Harcourt' => ['Nigeria', 4.8156, 7.0498], 'Benin City' => ['Nigeria', 6.3350, 5.6037], 'Enugu' => ['Nigeria', 6.4584, 7.5464],
        'Kaduna' => ['Nigeria', 10.5105, 7.4165], 'Ilorin' => ['Nigeria', 8.4966, 4.5426], 'Jos' => ['Nigeria', 9.8965, 8.8583],
        'Abeokuta' => ['Nigeria', 7.1475, 3.3619], 'Owerri' => ['Nigeria', 5.4836, 7.0333], 'Onitsha' => ['Nigeria', 6.1450, 6.7883],
        'Warri' => ['Nigeria', 5.5167, 5.7500], 'Calabar' => ['Nigeria', 4.9757, 8.3417], 'Uyo' => ['Nigeria', 5.0377, 7.9128],
        'Akure' => ['Nigeria', 7.2571, 5.2058], 'Osogbo' => ['Nigeria', 7.7827, 4.5418], 'Ado-Ekiti' => ['Nigeria', 7.6211, 5.2215],
        'Sokoto' => ['Nigeria', 13.0059, 5.2476], 'Maiduguri' => ['Nigeria', 11.8311, 13.1510], 'Zaria' => ['Nigeria', 11.0855, 7.7199],
        'Asaba' => ['Nigeria', 6.1980, 6.7330], 'Ogbomosho' => ['Nigeria', 8.1335, 4.2408], 'Yola' => ['Nigeria', 9.2035, 12.4954],
        'Makurdi' => ['Nigeria', 7.7322, 8.5391], 'Lokoja' => ['Nigeria', 7.8023, 6.7333], 'Minna' => ['Nigeria', 9.6139, 6.5569],
        'Bauchi' => ['Nigeria', 10.3158, 9.8442], 'Katsina' => ['Nigeria', 12.9908, 7.6017], 'Abakaliki' => ['Nigeria', 6.3249, 8.1137],
        'Awka' => ['Nigeria', 6.2104, 7.0741], 'Umuahia' => ['Nigeria', 5.5250, 7.4922], 'Aba' => ['Nigeria', 5.1066, 7.3667],
        'Ijebu-Ode' => ['Nigeria', 6.8194, 3.9173], 'Gombe' => ['Nigeria', 10.2897, 11.1673], 'Ile-Ife' => ['Nigeria', 7.4824, 4.5603],
        // Ghana, Kenya, Rwanda, South Africa and nearby
        'Accra' => ['Ghana', 5.6037, -0.1870], 'Kumasi' => ['Ghana', 6.6885, -1.6244], 'Tamale' => ['Ghana', 9.4008, -0.8393],
        'Takoradi' => ['Ghana', 4.8845, -1.7554], 'Cape Coast' => ['Ghana', 5.1053, -1.2466], 'Tema' => ['Ghana', 5.6698, -0.0166],
        'Nairobi' => ['Kenya', -1.2921, 36.8219], 'Mombasa' => ['Kenya', -4.0435, 39.6682], 'Kisumu' => ['Kenya', -0.0917, 34.7680],
        'Nakuru' => ['Kenya', -0.3031, 36.0800], 'Eldoret' => ['Kenya', 0.5143, 35.2698],
        'Kigali' => ['Rwanda', -1.9441, 30.0619], 'Huye' => ['Rwanda', -2.5967, 29.7394], 'Musanze' => ['Rwanda', -1.4998, 29.6347],
        'Johannesburg' => ['South Africa', -26.2041, 28.0473], 'Sandton' => ['South Africa', -26.1076, 28.0567],
        'Pretoria' => ['South Africa', -25.7479, 28.2293], 'Cape Town' => ['South Africa', -33.9249, 18.4241], 'Durban' => ['South Africa', -29.8587, 31.0218],
        'Kampala' => ['Uganda', 0.3476, 32.5825], 'Dar es Salaam' => ['Tanzania', -6.7924, 39.2083], 'Zanzibar' => ['Tanzania', -6.1659, 39.2026],
        'Addis Ababa' => ['Ethiopia', 9.0300, 38.7400], 'Lomé' => ['Togo', 6.1256, 1.2254], 'Cotonou' => ['Benin', 6.3703, 2.3912],
        'Dakar' => ['Senegal', 14.7167, -17.4677], 'Freetown' => ['Sierra Leone', 8.4657, -13.2317], 'Monrovia' => ['Liberia', 6.3156, -10.8074],
        'Douala' => ['Cameroon', 4.0511, 9.7679], 'Yaoundé' => ['Cameroon', 3.8480, 11.5021], 'Kinshasa' => ['DR Congo', -4.4419, 15.2663],
        'Lusaka' => ['Zambia', -15.3875, 28.3228], 'Harare' => ['Zimbabwe', -17.8252, 31.0335], 'Gaborone' => ['Botswana', -24.6282, 25.9231],
        // United Kingdom and Ireland
        'London' => ['United Kingdom', 51.5074, -0.1278], 'Croydon' => ['United Kingdom', 51.3762, -0.0982], 'Manchester' => ['United Kingdom', 53.4808, -2.2426],
        'Birmingham' => ['United Kingdom', 52.4862, -1.8904], 'Leeds' => ['United Kingdom', 53.8008, -1.5491], 'Liverpool' => ['United Kingdom', 53.4084, -2.9916],
        'Glasgow' => ['United Kingdom', 55.8642, -4.2518], 'Edinburgh' => ['United Kingdom', 55.9533, -3.1883], 'Bristol' => ['United Kingdom', 51.4545, -2.5879],
        'Leicester' => ['United Kingdom', 52.6369, -1.1398], 'Nottingham' => ['United Kingdom', 52.9548, -1.1581], 'Milton Keynes' => ['United Kingdom', 52.0406, -0.7594],
        'Reading' => ['United Kingdom', 51.4543, -0.9781], 'Coventry' => ['United Kingdom', 52.4068, -1.5197], 'Sheffield' => ['United Kingdom', 53.3811, -1.4701],
        'Cardiff' => ['United Kingdom', 51.4816, -3.1791], 'Belfast' => ['United Kingdom', 54.5973, -5.9301], 'Aberdeen' => ['United Kingdom', 57.1497, -2.0943],
        'Dublin' => ['Ireland', 53.3498, -6.2603],
        // United States
        'Houston' => ['United States', 29.7604, -95.3698], 'Atlanta' => ['United States', 33.7490, -84.3880], 'Silver Spring' => ['United States', 38.9907, -77.0261],
        'New York' => ['United States', 40.7128, -74.0060], 'Washington' => ['United States', 38.9072, -77.0369], 'Dallas' => ['United States', 32.7767, -96.7970],
        'Chicago' => ['United States', 41.8781, -87.6298], 'Los Angeles' => ['United States', 34.0522, -118.2437], 'Baltimore' => ['United States', 39.2904, -76.6122],
        'Philadelphia' => ['United States', 39.9526, -75.1652], 'Newark' => ['United States', 40.7357, -74.1724], 'Boston' => ['United States', 42.3601, -71.0589],
        'Minneapolis' => ['United States', 44.9778, -93.2650], 'Charlotte' => ['United States', 35.2271, -80.8431], 'Miami' => ['United States', 25.7617, -80.1918],
        'Seattle' => ['United States', 47.6062, -122.3321], 'San Francisco' => ['United States', 37.7749, -122.4194], 'Austin' => ['United States', 30.2672, -97.7431],
        'Columbus' => ['United States', 39.9612, -82.9988], 'Raleigh' => ['United States', 35.7796, -78.6382], 'Detroit' => ['United States', 42.3314, -83.0458],
        'Phoenix' => ['United States', 33.4484, -112.0740], 'Denver' => ['United States', 39.7392, -104.9903],
        // Canada
        'Toronto' => ['Canada', 43.6532, -79.3832], 'Mississauga' => ['Canada', 43.5890, -79.6441], 'Calgary' => ['Canada', 51.0447, -114.0719],
        'Montreal' => ['Canada', 45.5019, -73.5674], 'Vancouver' => ['Canada', 49.2827, -123.1207], 'Edmonton' => ['Canada', 53.5461, -113.4938],
        'Ottawa' => ['Canada', 45.4215, -75.6972], 'Winnipeg' => ['Canada', 49.8951, -97.1384], 'Halifax' => ['Canada', 44.6488, -63.5752],
        // Australia and New Zealand
        'Melbourne' => ['Australia', -37.8136, 144.9631], 'Sydney' => ['Australia', -33.8688, 151.2093], 'Perth' => ['Australia', -31.9505, 115.8605],
        'Brisbane' => ['Australia', -27.4698, 153.0251], 'Adelaide' => ['Australia', -34.9285, 138.6007], 'Canberra' => ['Australia', -35.2809, 149.1300],
        'Auckland' => ['New Zealand', -36.8485, 174.7633], 'Wellington' => ['New Zealand', -41.2865, 174.7762], 'Christchurch' => ['New Zealand', -43.5321, 172.6362],
        // The Gulf and Europe
        'Dubai' => ['United Arab Emirates', 25.2048, 55.2708], 'Abu Dhabi' => ['United Arab Emirates', 24.4539, 54.3773], 'Sharjah' => ['United Arab Emirates', 25.3463, 55.4209],
        'Doha' => ['Qatar', 25.2854, 51.5310], 'Paris' => ['France', 48.8566, 2.3522], 'Amsterdam' => ['Netherlands', 52.3676, 4.9041], 'Berlin' => ['Germany', 52.5200, 13.4050],
    ];

    /** [latitude, longitude] for a city name as people type it, or null. */
    public static function at(string $city): ?array
    {
        static $index = null;
        if ($index === null) {
            $index = [];
            foreach (self::CITIES as $name => [, $lat, $lng]) {
                $index[self::key($name)] = [$lat, $lng];
            }
        }
        return $index[self::key($city)] ?? null;
    }

    /** The listed city nearest a point: [name, country, km]. */
    public static function nearest(float $lat, float $lng): array
    {
        $best = ['', '', INF];
        foreach (self::CITIES as $name => [$country, $la, $lo]) {
            $d = self::km($lat, $lng, $la, $lo);
            if ($d < $best[2]) {
                $best = [$name, $country, $d];
            }
        }
        return $best;
    }

    /** Distance over the earth's surface, in kilometres. */
    public static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    private static function key(string $s): string
    {
        $s = strtolower(trim((string) (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s)));
        return (string) preg_replace('/[^a-z]+/', '', $s);
    }
}
