# The Last Arcana

## Run on Replit

- The project uses the existing PHP 8.2 runtime; no Composer packages, database, API keys, or external services are required.
- Click **Run** to start the **Start application** workflow.
- The workflow runs the PHP built-in server on `0.0.0.0:5000` with `router.php` so saved collection data is never served directly.
- Open Preview to play. `index.php` is the entry point, `style.css` provides styling, and `data/cards.json` contains the tarot cards.

## Current limitations

- Player status (level, EXP, HP, and stage) is stored in a PHP session and may reset when it expires. The Arcana Collection is saved separately in a JSON file and associated with a long-lived browser cookie.
- Collection data is kept in the hidden `data/.arcana/` directory so it is not served as a public web file. Clearing the browser's cookies or using another browser creates a separate collection.
- The stage action is prototype gameplay, not a finished combat system.
- PHP's built-in server is for development previews; use a production PHP server before a public production release.

## Project conventions

Keep the existing PHP, CSS, and JSON structure. No build step is needed.
