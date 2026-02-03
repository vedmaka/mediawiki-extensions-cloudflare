FROM mcr.microsoft.com/devcontainers/php:0-7.4

# Import Yarn GPG key to avoid "NO_PUBKEY" failures from yarnpkg.com
# (some base images add the Yarn APT source; ensure its key is present)
RUN set -eux; \
    if command -v curl >/dev/null 2>&1; then \
        curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - || true; \
    fi

# Ensure common utilities and compatibility libraries are present
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

USER vscode
