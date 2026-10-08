#!/usr/bin/env bash
set -euo pipefail

VERSION="9.6.38"
PHAR_URL="https://phar.phpunit.de/phpunit-${VERSION}.phar"
ASC_URL="https://phar.phpunit.de/phpunit-${VERSION}.phar.asc"
EXPECTED_SHA256="6b5235f282c6ccab3674ac27c5b1ddbd1da838e74bf07002ddcfd030d31298d7"
GPG_FINGERPRINT="D8406D0D82947747293778314AA394086372C20A"
GPG_KEY_URL="https://keys.openpgp.org/vks/v1/by-fingerprint/${GPG_FINGERPRINT}"

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TOOLS_DIR="${ROOT_DIR}/tools"
PHAR_PATH="${TOOLS_DIR}/phpunit-${VERSION}.phar"
ASC_PATH="${TOOLS_DIR}/phpunit-${VERSION}.phar.asc"

mkdir -p "${TOOLS_DIR}"

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "${TMP_DIR}"' EXIT

PHAR_TMP="${TMP_DIR}/phpunit.phar"
ASC_TMP="${TMP_DIR}/phpunit.phar.asc"

curl -fsSL -o "${PHAR_TMP}" "${PHAR_URL}"
curl -fsSL -o "${ASC_TMP}" "${ASC_URL}"

ACTUAL_SHA256="$(sha256sum "${PHAR_TMP}" | awk '{print $1}')"
if [[ "${ACTUAL_SHA256}" != "${EXPECTED_SHA256}" ]]; then
	echo "SHA-256 mismatch for PHPUnit PHAR." >&2
	echo "Expected: ${EXPECTED_SHA256}" >&2
	echo "Actual:   ${ACTUAL_SHA256}" >&2
	exit 1
fi

GNUPGHOME="${TMP_DIR}/gnupg"
mkdir -p "${GNUPGHOME}"
chmod 700 "${GNUPGHOME}"
export GNUPGHOME

curl -fsSL "${GPG_KEY_URL}" | gpg --batch --import

if ! gpg --batch --verify "${ASC_TMP}" "${PHAR_TMP}" 2>&1 | tee "${TMP_DIR}/gpg-verify.log"; then
	echo "GPG verification failed." >&2
	exit 1
fi

if ! grep -q "${GPG_FINGERPRINT}" "${TMP_DIR}/gpg-verify.log"; then
	echo "GPG signature fingerprint mismatch." >&2
	echo "Expected fingerprint: ${GPG_FINGERPRINT}" >&2
	exit 1
fi

mv -f "${PHAR_TMP}" "${PHAR_PATH}"
mv -f "${ASC_TMP}" "${ASC_PATH}"

echo "Verified PHPUnit ${VERSION} PHAR at ${PHAR_PATH}"
