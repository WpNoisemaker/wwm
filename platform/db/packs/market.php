<?php
/**
 * The marketplace sellers from the old site, restored as they were listed there, plus Organized
 * by Sheedah. Each gets a seller account, a verified business profile and their listings.
 *
 * AbujaCarhire and ViaskyNigeria are real businesses whose prices were never confirmed, so every
 * one of their listings says "price on request", exactly as on the old site. The same goes for
 * Organized by Sheedah. The emails are placeholders: change each one to the business's own in
 * the account's settings before handing the login over.
 *
 * Passwords are stored only as hashes. The owner was given the starting passwords directly.
 */
return [
    // login => [business, category, city, state, about, kind, every listing on request?, hash, listings, extras]
    'abujacarhire' => [
        'name' => 'AbujaCarhire', 'category' => 'Car hire', 'city' => 'Abuja', 'state' => 'FCT', 'kind' => 'ride', 'ask' => true,
        'about' => 'Car hire in Abuja for weddings: bridal cars, SUVs with drivers, coaches for guests & airport pick-ups.',
        'logo' => 'photos/listing-range-rover.jpg', 'hash' => '$2y$10$4rAKcnSrRWY9/EhxQzeBG.hEoYko4AjvoBDPWmsiOFxwY0Kt5kkhi',
        'listings' => [
            ['Black Range Rover Sport bridal car', 'day', 180000, 'Abuja', 'FCT', 'A clean, black Range Rover Sport to bring the bride to the ceremony in style, then take the couple to the reception.', ['Chauffeur in a suit', 'Ribbons & flowers on request', 'Up to 10 hours a day', 'Fuel within Abuja'], ['photos/listing-range-rover.jpg']],
            ['Mercedes-Benz G-Class with driver', 'day', 250000, 'Abuja', 'FCT', 'A G-Wagon for the couple, parents or VIP guests, with an experienced driver who knows every venue in town.', ['Professional driver', 'Fuel within Abuja', 'Air conditioning', 'Up to 12 hours a day'], ['photos/listing-g-class.jpg']],
            ['Luxury coach for up to 50 guests', 'day', 280000, 'Abuja', 'FCT', 'Move guests, the bridal train or family from the ceremony to the reception together, on time & in one go.', ['Driver & fuel within Abuja', 'Air conditioning', 'Room for outfits & gele boxes'], ['photos/listing-coach.jpg']],
            ['Airport pick-up from Nnamdi Azikiwe Airport', 'trip', 35000, 'Abuja', 'FCT', 'Meet & greet for guests flying in, then a smooth ride to their hotel or apartment.', ['Driver waits at arrivals with a name sign', 'Help with luggage', 'Flight tracking, so late flights are fine'], ['photos/listing-airport.jpg']],
        ],
    ],
    'viaskynigeria' => [
        'name' => 'ViaskyNigeria', 'category' => 'Travel', 'city' => '', 'state' => '', 'kind' => 'honeymoon', 'ask' => true,
        'about' => 'Honeymoon packages, flights & visa help for couples & their guests.',
        'logo' => 'photos/listing-flights.jpg', 'hash' => '$2y$10$KCwM85LSJVTd7OFtdGP1PetiqnwMkvv1T.2r1IQBBRGSR5CU6sLp6',
        'listings' => [
            ['Zanzibar honeymoon, 5 nights for two', 'package', 2450000, 'Zanzibar', 'Tanzania', 'White sand, warm water & slow mornings. A beachfront stay with a spice tour & a sunset dhow cruise.', ['Return flights from Lagos or Abuja', '5 nights beachfront hotel with breakfast', 'Airport transfers', 'Spice tour & sunset dhow cruise'], ['photos/listing-zanzibar.jpg']],
            ['Dubai honeymoon, 4 nights for two', 'package', 3100000, 'Dubai', 'UAE', 'City lights, desert dunes & a dinner cruise on the marina.', ['Return flights', '4 nights 4-star hotel with breakfast', 'Desert safari with dinner', 'Visa help'], ['photos/listing-dubai.jpg']],
            ['Cape Town honeymoon, 6 nights for two', 'package', 3650000, 'Cape Town', 'South Africa', 'Table Mountain, wine farms & Cape Point, at an easy pace.', ['Return flights', '6 nights boutique hotel', 'Winelands day trip', 'Cape Point tour'], ['photos/listing-cape-town.jpg']],
            ['Flight & visa help for guests flying in', 'person', 25000, 'Lagos', 'Lagos', 'For guests coming home for the wedding: we find the best fares & help with visa letters & bookings.', ['Best-fare search', 'Visa letter help', 'Booking changes handled for you'], ['photos/listing-flights.jpg'], 'service'],
        ],
    ],
    'ixprojects' => [
        'name' => 'IX-Projects', 'category' => 'Stays', 'city' => 'Lagos', 'state' => 'Lagos', 'kind' => 'stay', 'ask' => false,
        'about' => 'Serviced apartments in Lekki & Ikoyi for couples, families & guests in town for the wedding.',
        'logo' => 'photos/listing-apartment.jpg', 'hash' => '$2y$10$ky3c/NA6C0UCD56kWLnA4.VwhzlGhJoLL9BFwF0XmW6.8BuBuozgm', 'slug' => 'ix-projects',
        'listings' => [
            ['3-bed apartment in Lekki Phase 1', 'night', 185000, 'Lagos', 'Lagos', 'A bright, serviced apartment close to the big Lekki venues. Space for the bride, her mum & the make-up artist on the morning.', ['24-hour power & security', 'Fast Wi-Fi', 'Full kitchen', 'Parking for 2 cars'], ['photos/listing-apartment.jpg']],
            ['Studio in Ikoyi near the wedding venues', 'night', 85000, 'Lagos', 'Lagos', 'A quiet studio for one or two guests, ten minutes from most Ikoyi & Victoria Island venues.', ['24-hour power', 'Wi-Fi', 'Smart TV', 'Daily cleaning'], ['photos/listing-studio.jpg']],
            ['5-bed family house in Lekki', 'night', 350000, 'Lagos', 'Lagos', 'Keep the whole family under one roof for the wedding week, with room to dress, rehearse & eat together.', ['24-hour power & security', 'Garden for photos', 'Cook on request', 'Parking for 4 cars'], ['photos/listing-family-house.jpg']],
        ],
    ],
    'cedarcourt' => [
        'name' => 'Cedar Court Villas', 'category' => 'Stays', 'city' => 'Accra', 'state' => 'Greater Accra', 'kind' => 'stay', 'ask' => false,
        'about' => 'Private villas in Accra for wedding parties & families flying in.',
        'logo' => 'photos/listing-villa.jpg', 'hash' => '$2y$10$xBSjDiEVcx0NIN2oCVC6GerDt9bYWSaHFkpjvKUkPImm6HvPdqKSi',
        'listings' => [
            ['4-bed villa with pool in East Legon', 'night', 420000, 'Accra', 'Greater Accra', 'A private villa for the couple & their closest family, with a pool for the after-party.', ['Pool', 'Backup power', 'Housekeeping', 'Airport pick-up on request'], ['photos/listing-villa.jpg']],
        ],
    ],
    'ebilace' => [
        'name' => 'Ẹbí Lace & Gele', 'category' => 'Aso-ebi', 'city' => 'Lagos', 'state' => 'Lagos', 'kind' => 'asoebi', 'ask' => false,
        'about' => 'We source asoebi for Nigerian & African weddings: lace, aso-oke, ankara & gele for the couple, the family & every guest. Send us your colours, we match them, buy in bulk, then sort & label each guest’s fabric by name & send it to them, in Nigeria or abroad.',
        'logo' => 'photos/listing-fabric-stall.jpg', 'hash' => '$2y$10$jmzfSl2CdBMNZrHE1u8W0.uF1egzyV7tgttJT2jjwyBGc40yUPO.O', 'slug' => 'ebi-lace-gele',
        'listings' => [
            ['Lace asoebi with matching gele', 'person', 75000, 'Lagos', 'Lagos', 'Five yards of lace & a matching gele for each guest, in your colours. Send us a photo or a colour code & we match it before you pay for the lot.', ['5 yards of lace per guest', 'Matching gele', 'Colour matched to your sample', 'Packed & labelled with each guest’s name'], ['photos/listing-olive-gele.jpg', 'photos/listing-coral-gele.jpg']],
            ['Hand-woven aso-oke for the couple', 'package', 350000, 'Lagos', 'Lagos', 'Aso-oke woven to order for the bride & groom in your colours: iro, buba, gele & ipele for the bride, agbada, sokoto & fila for the groom.', ['Woven to order in your colours', 'Bride: iro, buba, gele & ipele', 'Groom: agbada, buba, sokoto & fila', 'Ready in 3 to 4 weeks'], ['photos/listing-couple-olive.jpg', 'photos/listing-couple-magenta.jpg']],
            ['Ankara asoebi for family & friends', 'person', 18500, 'Lagos', 'Lagos', 'Bright, good-quality ankara for a big crowd on a budget. Six yards per guest, with plenty of prints to pick from.', ['6 yards per guest', 'Wax prints & local brands', 'Lower price from 30 guests'], ['photos/listing-ankara-rolls.jpg', 'photos/listing-wax-prints.jpg']],
            ['Gele for the bride, the mums & the bridal train', 'person', 12000, 'Lagos', 'Lagos', 'Gele in your colours for the bride, both mothers & the bridal train, with a gele stylist on the morning if you are in Lagos.', ['Damask, aso-oke or sego gele', 'Colour matched', 'Gele stylist on the morning (Lagos)'], ['photos/listing-red-gele.jpg', 'photos/listing-green-gele.jpg']],
            ['Bulk asoebi for 50+ guests, shipped worldwide', 'package', 0, 'Lagos', 'Lagos', 'For big weddings: we buy your fabric in bulk, sort & label it for each guest & send it to them in Nigeria, the UK, the US & Canada. Tell us how many guests & your colours & we send you a price.', ['Bulk fabric at market prices', 'Sorted & labelled for each guest', 'Delivery in Nigeria & abroad', 'Price sent to you directly'], ['photos/listing-fabric-stall.jpg', 'photos/listing-fabric-close.jpg', 'photos/listing-guests-magenta.jpg'], 'asoebi', true],
        ],
    ],
    'organizedbysheedah' => [
        'name' => 'Organized by Sheedah', 'category' => 'Home organising', 'city' => '', 'state' => '', 'kind' => 'home', 'ask' => true,
        'about' => 'Professional home organising. We organise apartments, wardrobes and kitchens, and help couples pack up and settle into their new home after the wedding.',
        'logo' => 'photos/planner-phone.jpg', 'hash' => '$2y$10$gLWnUwPXEfWB97hTlic51OQCEBUe8MhrANo.8wOM.J/5jjpefWPbq',
        'listings' => [
            ['Move-in organising for newlyweds', '', 0, '', '', 'Moving into your first home together? We unpack, sort and organise the new apartment room by room, so married life starts without the boxes.', ['Unpacking and putting away', 'Kitchen, wardrobe and storage set up', 'A home that is easy to keep tidy'], ['photos/listing-apartment.jpg']],
            ['Packing for the move', '', 0, '', '', 'Leaving two homes for one? We pack, sort and label everything before the move, so it arrives ready to be unpacked in the right room.', ['Sorting what comes and what goes', 'Packed and labelled by room', 'Ready for the movers'], ['photos/listing-family-house.jpg']],
        ],
    ],
];
