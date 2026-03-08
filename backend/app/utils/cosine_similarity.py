# utils/cosine_similarity.py
"""Cosine similarity for text comparison — mirrors legacy CosineSimilarityChecker.php."""
import re
import math
from typing import Dict


def normalize_text(text: str) -> str:
    """Normalize text: lowercase, strip non-alphanumeric."""
    return re.sub(r"[^a-z0-9]+", " ", text.strip().lower())


def text_to_vector(text: str) -> Dict[str, int]:
    """Convert text to word frequency vector."""
    words = normalize_text(text).split()
    vec: Dict[str, int] = {}
    for w in words:
        if w:
            vec[w] = vec.get(w, 0) + 1
    return vec


def compute(vec1: Dict[str, int], vec2: Dict[str, int]) -> float:
    """Compute cosine similarity between two vectors."""
    dot = sum(vec1.get(k, 0) * vec2.get(k, 0) for k in set(vec1) | set(vec2))
    norm_a = math.sqrt(sum(v ** 2 for v in vec1.values()))
    norm_b = math.sqrt(sum(v ** 2 for v in vec2.values()))
    if norm_a == 0 or norm_b == 0:
        return 0.0
    return dot / (norm_a * norm_b)


def similarity(text1: str, text2: str) -> float:
    """Compute cosine similarity between two texts."""
    return compute(text_to_vector(text1), text_to_vector(text2))
