# utils/encryption.py
"""AES-256-CBC encryption mirroring legacy PHP encryption_functions.php."""
import base64
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from cryptography.hazmat.primitives import padding as sym_padding
from cryptography.hazmat.backends import default_backend

from core.config import ENCRYPTION_KEY, ENCRYPTION_IV


def _get_cipher():
    return Cipher(
        algorithms.AES(ENCRYPTION_KEY.encode()),
        modes.CBC(ENCRYPTION_IV.encode()),
        backend=default_backend(),
    )


def encrypt_data(plaintext: str) -> str:
    """Encrypt data using AES-256-CBC, return base64(base64(ciphertext)) to match PHP."""
    padder = sym_padding.PKCS7(128).padder()
    padded = padder.update(plaintext.encode()) + padder.finalize()
    encryptor = _get_cipher().encryptor()
    ct = encryptor.update(padded) + encryptor.finalize()
    # PHP openssl_encrypt returns base64, then we base64 again
    inner_b64 = base64.b64encode(ct).decode()
    return base64.b64encode(inner_b64.encode()).decode()


def decrypt_data(encrypted: str) -> str:
    """Decrypt data that was encrypted with encrypt_data."""
    inner_b64 = base64.b64decode(encrypted).decode()
    ct = base64.b64decode(inner_b64)
    decryptor = _get_cipher().decryptor()
    padded = decryptor.update(ct) + decryptor.finalize()
    unpadder = sym_padding.PKCS7(128).unpadder()
    return (unpadder.update(padded) + unpadder.finalize()).decode()
