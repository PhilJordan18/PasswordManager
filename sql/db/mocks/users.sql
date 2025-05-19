-- INSERT INTO users (username, firstname, lastname, email, password, balance, type)
-- VALUES
--     ('john_doe', 'John', 'Doe', 'john.doe@example.com', MD5('password123')),
--     ('jane_smith', 'Jane', 'Smith', 'jane.smith@example.com', MD5('realhardpassword'), 500.50, 'PREMIUM'),
--     ('alice_wonder', 'Alice', 'Wonder', 'alice.wonder@example.com', MD5('whohardasiam'), 0.00, 'NORMAL'),
--     ('bob_builder', 'Bob', 'Builder', 'bob.builder@example.com', MD5('thuglife4realog'), 750.25, 'PREMIUM');
--
INSERT INTO users (username, firstname, lastname, email, password, created_at, updated_at)
VALUES (
        'phil_jordan', 'Phil', 'Jordan', 'phil.jordan@email.com', MD5('pA$$wordaccount19.'), NOW(), NOW()
       )