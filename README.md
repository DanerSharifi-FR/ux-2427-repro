# Symfony UX Autocomplete #2427 Reproduction

Minimal reproduction of [symfony/ux#2427](https://github.com/symfony/ux/issues/2427).

The issue affects a **multiple AJAX autocomplete**: after selecting a filtered result, the input is cleared but the remote choices are not refreshed.

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
2. Type `co`.
3. Select **Cookies**.
4. Reopen the field without typing anything.

The input is empty, but the complete list is not reloaded.

The Network panel shows:

```text
/foods-autocomplete?query=
/foods-autocomplete?query=co
```

No new empty-query request is made after selecting **Cookies**.

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

The `reproduce-2427-old-version` branch reproduces the same issue with an older Symfony UX stack.
