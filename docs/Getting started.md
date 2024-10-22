# Getting started

Welcome! This guide will help you set up your development environment and get Hypixel.Paniek.de running locally.

1. **Clone the repository**

   ```sh
   git clone https://github.com/MaxKorlaar/hypixel-signatures.git
   cd hypixel-signatures
   ```

## Using dev containers

1. **Open the project in a dev container**

   - [Instructions for PhpStorm](https://www.jetbrains.com/help/phpstorm/connect-to-devcontainer.html)
   - Instructions for VS Code: open the project in VS Code Press `F1` and select `Dev Containers: Open Folder in Container` (Source: [Quick start: Open an existing folder in a container](https://code.visualstudio.com/docs/devcontainers/containers#_quick-start-open-an-existing-folder-in-a-container))

## Locally

`// TODO`

## Running

1. **Run a bunch of one-time commands**

   Note that if you're using pnpm, execute `pnpm install --shamefully-hoist` instead of `pnpm install`.

   ```sh
   cp .env.example .env
   pnpm install --shamefully-hoist
   pnpm run development
   composer install
   php artisan key:generate
   php artisan ide-helper:generate
   ```
2. **Set up the Hypixel API key**

   You need to provide a Hypixel API key for the website to work. You can get one at https://developers.hypixel.net/.

   ```sh
   HYPIXEL_API_KEY=<your-key-here>
   ```

3. **Start the development server**

   You can start the development server using the following command:

   ```sh
   php artisan serve
   ```

   You can now open your browser and navigate to http://localhost:8000.
