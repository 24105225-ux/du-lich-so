FROM python:3.12-slim
WORKDIR /app
COPY python-service/requirements.txt ./requirements.txt
RUN pip install --no-cache-dir -r requirements.txt
COPY python-service/app ./app
COPY python-service/data ./data
EXPOSE 8001
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
