from google_auth_oauthlib.flow import InstalledAppFlow
import json

SCOPES = ['https://www.googleapis.com/auth/gmail.send']

def main():
    flow = InstalledAppFlow.from_client_secrets_file('credentials.json', SCOPES)
    creds = flow.run_local_server(port=8080)  

    with open('token_send.json', 'w') as token_file:
        token_file.write(creds.to_json())

    print("token_send.json generated successfully.")

if __name__ == '__main__':
    main()
