# pokemon_it
Pokemon Listing for test purpose.

This is a Pokemon Listing page, for test purposes. The list of pokemons has been acquired with the Pokemon V2 API (https://pokeapi.co/docs/v2)
and is updated (if there are new pokemons) with every visit, directly from the API.

It is required that you build an SQL database (MySQL, MariaDB etc.) with the dump file included in this repo.
Also, you must declare the database connection information in the "apokedata.php". The database has already filled in the Pokemons till 03/2022
and also has records of favorites as a demo.

The starting page is "index.php" which loads the main process page "apokemain.php".
The page "apokefav_api.php" is the AJAX listener.

The progam lists all available Pokemons (not Mega Evolution) with a default limit of 20. The backend is ready to accept custom limits,
but a selector must be added (e.g. drop down select), named "limit". The default sort order is by weight. Then information for each pokemon
shown are: Height, Weight, Id, Name, Type (colored), Species, Abilities, Image (front), Stats (Speed, Sp. Defense, Sp. Attack, Defense, Attack, Hp).

This test featurs an interactive favorite button, which notes your favorite pokemons and makes a record in the database. As this is for test purposes,
the favorites are showing for all users the same. In the database there is a field calls 'pokemon_user', which can hold a user id for instance so you
can have personalized favorites. In the pages "apokefav_api.php" and "apokecalls.php" you have to replace the string 'demo' with the user's id.
For the AJAX call, it is also passed a hashed string for checking that the call was made from an active user, for security purpose.

Other features are a dynamic search for Pokemon names (both select from list or search with keyword), a "Sort By" selector with various options
(e.g. list by Speed and descenting order) and also a selector for listing specific Types of Pokemons.
In the "Sort By" you can add other options or sort order, adding your options in the file "apokedata.php" both in $sql_sort_filter and $slc_sort_filter arrays.
The types of Pokemons are gathered from the database (list of Pokemons grabbed from the Pokemon API), so if there are any new Types, they will be on auto update.

The page is responsive as the units mostly used in html/css have been set to viewport sizes.
