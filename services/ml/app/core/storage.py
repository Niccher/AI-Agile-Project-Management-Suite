"""S3-compatible object storage helper using boto3.

Enables downloading user attachments for document processing and uploading
generated reports/artifacts directly to S3/MinIO.
"""

from __future__ import annotations

import logging
from pathlib import Path
from typing import Any

from app.config import settings

logger = logging.getLogger(__name__)

_s3_client: Any = None


def get_s3_client():
    """Retrieve or lazily initialize the boto3 S3 client."""
    global _s3_client
    if _s3_client is not None:
        return _s3_client

    try:
        import boto3
        from botocore.client import Config

        _s3_client = boto3.client(
            "s3",
            endpoint_url=settings.S3_ENDPOINT,
            aws_access_key_id=settings.S3_ACCESS_KEY,
            aws_secret_access_key=settings.S3_SECRET_KEY,
            region_name=settings.S3_REGION,
            config=Config(signature_version="s3v4", s3={"addressing_style": "path"}),
        )
        return _s3_client
    except ImportError:
        logger.warning("boto3 is not installed; S3 storage functionality will be disabled.")
        return None
    except Exception as exc:
        logger.error("Failed to initialize S3 client: %s", exc)
        return None


class S3Storage:
    """Wrapper class providing common S3/MinIO operations for the ML pipeline."""

    @staticmethod
    def is_available() -> bool:
        """Check if S3 client is configured and importable."""
        return get_s3_client() is not None

    @classmethod
    def download_file(cls, bucket: str, key: str, dest_path: str | Path) -> Path:
        """Download an S3 object to a local destination file path."""
        client = get_s3_client()
        if client is None:
            raise RuntimeError("S3 client is not available")

        dest = Path(dest_path)
        dest.parent.mkdir(parents=True, exist_ok=True)
        logger.info("Downloading s3://%s/%s to %s", bucket, key, dest)
        client.download_file(bucket, key, str(dest))
        return dest

    @classmethod
    def get_object_bytes(cls, bucket: str, key: str) -> bytes:
        """Fetch raw bytes of an S3 object into memory."""
        client = get_s3_client()
        if client is None:
            raise RuntimeError("S3 client is not available")

        response = client.get_object(Bucket=bucket, Key=key)
        return response["Body"].read()

    @classmethod
    def upload_file(
        cls,
        src_path: str | Path,
        bucket: str,
        key: str,
        content_type: str | None = None,
    ) -> str:
        """Upload a local file to S3 and return the object key."""
        client = get_s3_client()
        if client is None:
            raise RuntimeError("S3 client is not available")

        src = Path(src_path)
        extra_args: dict[str, Any] = {}
        if content_type:
            extra_args["ContentType"] = content_type

        logger.info("Uploading %s to s3://%s/%s", src, bucket, key)
        client.upload_file(str(src), bucket, key, ExtraArgs=extra_args if extra_args else None)
        return key

    @classmethod
    def upload_bytes(
        cls,
        data: bytes,
        bucket: str,
        key: str,
        content_type: str = "application/octet-stream",
    ) -> str:
        """Upload in-memory bytes to S3 and return the object key."""
        client = get_s3_client()
        if client is None:
            raise RuntimeError("S3 client is not available")

        logger.info("Uploading %d bytes to s3://%s/%s", len(data), bucket, key)
        client.put_object(
            Bucket=bucket,
            Key=key,
            Body=data,
            ContentType=content_type,
        )
        return key

    @classmethod
    def generate_presigned_url(
        cls, bucket: str, key: str, expires_in: int = 3600
    ) -> str:
        """Generate a pre-signed GET URL for direct browser access."""
        client = get_s3_client()
        if client is None:
            raise RuntimeError("S3 client is not available")

        return client.generate_presigned_url(
            "get_object",
            Params={"Bucket": bucket, "Key": key},
            ExpiresIn=expires_in,
        )
