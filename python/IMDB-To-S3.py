#!/usr/bin/env python
# coding: utf-8

# In[1]:


import gzip
import shutil
import urllib.request


# In[2]:


def download_gz(url, filename):
    """
    downloads a GunZip file from the url given and writes it to the local filesystem
    :param url: the url of the GunZipped file to download
    :param filename: the filename to write to the local filesystem
    :return: None
    """
    
    if url is None or not url.endswith(".gz"):
        raise ValueError("'url' is invalid")
    
    print(f"Downloading: {url}")
    
    with urllib.request.urlopen(url) as response, open(filename, 'wb') as output_file:
        with gzip.GzipFile(fileobj=response) as uncompressed:
            shutil.copyfileobj(uncompressed, output_file)
            
            print(f"Downloaded: {filename}")
            print()


# In[3]:


def s3_upload(filename):
    """
    uploads a file from the local filesystem to S3
    :param filename: the filename to write to upload to S3
    :return: None
    """
    
    print(f"Uploading: {filename}")
    
    bucket_name = "wheeler-cloud-guru-challenges"
    object_name = f"1020/IMDB/{filename}"
    
    get_ipython().system('aws s3 cp "{filename}" "s3://{bucket_name}/{object_name}"')
    
    print(f"Uploaded: s3://{bucket_name}/{object_name}")
    print()


# In[4]:


imdb_root = "https://datasets.imdbws.com"
imdb_sets = [
    ("name.basics.tsv.gz"     , "name_basics.tsv"     ),
    ("title.akas.tsv.gz"      , "title_akas.tsv"      ),
    ("title.basics.tsv.gz"    , "title_basics.tsv"    ),
    ("title.crew.tsv.gz"      , "title_crew.tsv"      ),
    ("title.episode.tsv.gz"   , "title_episode.tsv"   ),
    ("title.principals.tsv.gz", "title_principals.tsv"),
    ("title.ratings.tsv.gz"   , "title_ratings.tsv"   )
]


# In[5]:


for name_pair in imdb_sets:
    src_name = name_pair[0]
    dst_name = name_pair[1]
    
    download_gz(f"{imdb_root}/{src_name}", dst_name)
    s3_upload(dst_name)


# In[ ]:




