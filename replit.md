# The Last Arcana

## Run on Replit

- The project uses the existing PHP 8.2 runtime; no Composer packages, database, API keys, or external services are required.
- Click **Run** to start the **Start application** workflow.
- The workflow runs `php -S 0.0.0.0:5000 -t .` from the project root.
- Open Preview to play. `index.php` is the entry point, `style.css` provides styling, and `data/cards.json` contains the tarot cards.

## Current limitations

- Player progress is stored in PHP sessions, not permanent storage. It may disappear when the session expires or the runtime is replaced.
- The stage action is prototype gameplay, not a finished combat system.
- PHP's built-in server is for development previews; use a production PHP server before a public production release.

## Project conventions

Keep the existing PHP, CSS, and JSON structure. No build step is needed.
