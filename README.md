# README #

Misc PHP scripts

### List ###

* laravel_lang_sorter.php - Sort and beautify Laravel's lang file. It can be either *.php or *.json file.



### Manual ###

Basic usage of laravel_lang_sorter.php:
```
php laravel_lang_sorter.php /path/to/laravel/lang/file.php
```

Previously specified command would read, sort, beautify and finally output 
result to the STDOUT.

To sort and beautify all lang files (to the lowest depth, recursively) from 
laravel's lang directory, that is to replace old content with new content use:
```
php laravel_lang_sorter.php --recursive --inplace /path/to/laravel/lang/folder
```

If in previous example we remove --recursive option then this command would
only loop through files on the first level of the specified directory.

Example of .php file contents conversion from e.g.:
```
<?php
return [
    'videos-count' => "Number of videos",
    'clients-count' => "Number of clients",
    'livestreams-count' => "Number of livestreams",
    'artists-count' => "Number of artists",
    'goto-videos' => "Go to all videos...",
    'goto-clients' => "Go to all clients...",
    'goto-livestreams' => "Go to all livestreams...",
    'goto-artists' => "Go to all artists...",
    'last-entries-title' => "Last Entries",
    'info-counts-title' => "Resources / counts",
    'video-upload-count-chart-title' => "Video upload per month",
    'video-upload-count-chart-subtitle' => "Videos uploaded by both artists and clients",
    'clients-created-count-chart-title' => "New clients registered per month",
    'clients-created-count-chart-subtitle' => "This year's new registered client count",
    'voting-grade' => "G.",
    'voting-creators' => "Creators",
    'voting-title' => "Title",
    'pie-chart-legend-title' => "Clients by type",
    'pie-chart-subtitle' => "Ratio of subscribed/premium clients",
    'last-entry-artist' => "Last artist entry",
    'last-entry-video' => "Last video entry",
    'last-entry-livestream' => "Last livestream entry",
    'last-entry-voting' => "Last finished voting"
    
];
```
to
```
<?php
return [
	'artists-count'                        => 'Number of artists',
	'clients-count'                        => 'Number of clients',
	'clients-created-count-chart-subtitle' => 'This year's new registered client count',
	'clients-created-count-chart-title'    => 'New clients registered per month',
	'goto-artists'                         => 'Go to all artists...',
	'goto-clients'                         => 'Go to all clients...',
	'goto-livestreams'                     => 'Go to all livestreams...',
	'goto-videos'                          => 'Go to all videos...',
	'info-counts-title'                    => 'Resources / counts',
	'last-entries-title'                   => 'Last Entries',
	'last-entry-artist'                    => 'Last artist entry',
	'last-entry-livestream'                => 'Last livestream entry',
	'last-entry-video'                     => 'Last video entry',
	'last-entry-voting'                    => 'Last finished voting',
	'livestreams-count'                    => 'Number of livestreams',
	'pie-chart-legend-title'               => 'Clients by type',
	'pie-chart-subtitle'                   => 'Ratio of subscribed/premium clients',
	'video-upload-count-chart-subtitle'    => 'Videos uploaded by both artists and clients',
	'video-upload-count-chart-title'       => 'Video upload per month',
	'videos-count'                         => 'Number of videos',
	'voting-creators'                      => 'Creators',
	'voting-grade'                         => 'G.',
	'voting-title'                         => 'Title',
];
```
or from contents of JSON lang file e.g:
```
{
    "remove": "izbriši",
    "cancel": "ignoriši izmene",
    "free": "besplatno",
    "Artists": "Umetnici",
    "Live": "Uživo",
    "Voting": "Glasanje",
    "Paid": "Plaća se",
    "Free": "Besplatno",
    "Videos": "Video",
    "Top Videos": "Vrh Video",
    "Live stream": "Live stream",
    "Comments": "Komentari",
    "Write a comment": "Napišite Vaš komentar",
    "view": "pregleda",
    "Genres": "Žanrovi",
    "Groups": "Grupe",
    "Help": "Pomoć",
    "Notifications": "Obaveštenja",
    "Overview": "Pregled"
}
```
to
```
{
    "Artists": "Umetnici",
    "cancel": "ignoriši izmene",
    "Comments": "Komentari",
    "free": "besplatno",
    "Free": "Besplatno",
    "Genres": "Žanrovi",
    "Groups": "Grupe",
    "Help": "Pomoć",
    "Live": "Uživo",
    "Live stream": "Live stream",
    "Notifications": "Obaveštenja",
    "Overview": "Pregled",
    "Paid": "Plaća se",
    "remove": "izbriši",
    "Top Videos": "Vrh Video",
    "Videos": "Video",
    "view": "pregleda",
    "Voting": "Glasanje",
    "Write a comment": "Napišite Vaš komentar"
}
```



