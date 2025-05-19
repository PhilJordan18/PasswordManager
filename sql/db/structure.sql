CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    firstname TEXT NOT NULL,
    lastname TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone_number TEXT,
    password TEXT NOT NULL,
    master_key TEXT NOT NULL,
    public_key TEXT NOT NULL,
    private_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE tokens (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    token TEXT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL CHECK (expires_at > created_at),
    revoked BOOLEAN DEFAULT FALSE
);

CREATE TABLE Applications(
    id SERIAL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL,
    link TEXT NOT NULL,
    icon TEXT
);

CREATE TABLE Accounts (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES Users(id) ON DELETE CASCADE,
    app_id INT REFERENCES Applications(id) ON DELETE CASCADE,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, app_id, username)
);

CREATE TABLE SharedPassword(
    id SERIAL PRIMARY KEY,
    sender_id INT REFERENCES Users(id) ON DELETE CASCADE,
    receiver_id INT REFERENCES Users(id) ON DELETE CASCADE,
    account_id INT REFERENCES Accounts(id) ON DELETE CASCADE,
    encrypted_password TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

