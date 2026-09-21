# Symfony UX Autocomplete #2427 Reproduction

Minimal reproduction of [symfony/ux#2427](https://github.com/symfony/ux/issues/2427).

The issue affects a **multiple AJAX autocomplete**: after selecting a filtered result, the input is cleared but the remote choices are not refreshed. This example disables the default focus preload so an earlier unfiltered response cannot mask the issue.

## Run the reproduction

Clone the repository and switch to the latest-version reproduction branch:

```bash
git clone https://github.com/DanerSharifi-FR/ux-2427-repro.git
cd ux-2427-repro
git switch test-2427-latest
```

Build the Docker image so the required PHP and Symfony environment is available:

```bash
docker compose build
```

Install the PHP dependencies inside the container:

```bash
docker compose run --rm \
  --user "$(id -u):$(id -g)" \
  -e HOME=/tmp \
  app composer install
```

Now launch the Symfony application:

```bash
docker compose up
```

Open:

```text
http://localhost:8000
```

## Reproduce the issue

1. Click the **Foods** autocomplete.
2. Type `coo` (the default minimum is three characters).
3. Select **Cookies**.
4. Reopen the field without typing anything.

The input is empty, but the complete list is not reloaded.

The Network panel shows:

```text
/foods-autocomplete?query=coo
```

No empty-query request is made after selecting **Cookies**, and the dropdown is empty when reopened even though Cooking, Coconut, Tea, and Apple are available.

Now type `x` and remove it with Backspace. This triggers:

```text
/foods-autocomplete?query=x
/foods-autocomplete?query=
```

The complete remaining list is then loaded again.

## Stack

- Symfony 8.1
- Symfony UX Autocomplete 3.4
- StimulusBundle 3.4
- Tom Select 2.6.2

## Testing the Autocomplete fix for issue #2427

### Test the fork in the reproduction project

The commands below assume `symfony-ux` and `ux-2427-repro` are sibling directories.

1. Build the controller in the Symfony UX fork:

    ```bash
    cd ~/IMT/s7-open-source/symfony-ux/src/Autocomplete/assets
    node ../../../bin/build_package.ts .
    ```

2. In `ux-2427-repro/compose.yaml`, mount the fork alongside the project:

    ```yaml
    services:
        app:
            volumes:
                - .:/app
                - ../symfony-ux:/symfony-ux
    ```

3. In `ux-2427-repro/composer.json`, add a top-level `repositories` entry after `prefer-stable`:

    ```json
    "repositories": [
        {
            "type": "path",
            "url": "/symfony-ux/src/Autocomplete",
            "options": {
                "symlink": true,
                "versions": {
                    "symfony/ux-autocomplete": "3.5.1"
                }
            }
        }
    ],
    ```

4. Update the package once and check that Composer linked the fork:

    ```bash
    cd ~/IMT/s7-open-source/ux-2427-repro
    docker compose run --rm app composer update symfony/ux-autocomplete
    docker compose run --rm app readlink -f vendor/symfony/ux-autocomplete
    ```

   The second command should print `/symfony-ux/src/Autocomplete`. After later TypeScript changes, rebuild `dist` in step 1; the Composer update does not need to be repeated.

5. Start the project and open <http://localhost:8000>:

    ```bash
    docker compose up --force-recreate
    ```

   Hard-refresh the page. In **Foods**, type `coo`, select **Cookies**, and reopen the dropdown. The remaining choices should appear, and the browser Network tab should show a new `/foods-autocomplete?query=` request.

   To check the explicit minimum-character case, add `'min_characters' => 3,` beside `'preload' => false,` in `ux-2427-repro/src/Controller/ReproController.php`. Repeat the steps above. **Cooking** should no longer appear after reopening, and no empty-query request should be sent.

## Run the controller unit tests

From the fork's `src/Autocomplete/assets` directory, run the focused regression test first:

```bash
cd ~/IMT/s7-open-source/symfony-ux/src/Autocomplete/assets
./node_modules/.bin/vitest --run test/unit/controller.test.ts -t "respects an explicit minimum character count after selecting a remote option"
```

Then run the full controller test file:

```bash
./node_modules/.bin/vitest --run test/unit/controller.test.ts
```

