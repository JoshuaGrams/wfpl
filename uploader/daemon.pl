#!/usr/bin/perl

# FIXME rewrite to use non-blocking IO and put limits on waiting

#use Fcntl;

#$flags = '';
#fcntl(HANDLE, F_GETFL, $flags)
#    or die "Couldn't get flags for HANDLE : $!\n";
#$flags |= O_NONBLOCK;
#fcntl(HANDLE, F_SETFL, $flags)
#    or die "Couldn't set flags for HANDLE: $!\n";
#
#Once a filehandle is set for non-blocking I/O, the sysread or syswrite calls that would block will instead return undef and
#set $! to EAGAIN:


use strict;

use vars qw($output_path $g_filename $flags $buffer $the_end $refills_at_end $content_length $bytes_written $progress_written $bytes_left $boundary);

$output_path = $ARGV[0];

$the_end = 0;
$content_length = -1;
$boundary = '';
$refills_at_end = 0;
$bytes_left = 4000; # if the headers are bigger than this... too bad
# FIXME if the entire request (including file contents) is less than 4000 this causes the program to hang. This happens with firefox when the file is deleted before hitting submit

sub refill_buffer {
	my $ret;
	my $size;
	my $max_read;
	$size = length $buffer;
	if($the_end == 1 || $bytes_left - $size < 1) {
		$refills_at_end += 1;
		if($refills_at_end > 10) {
			die('refill_buffer called too many times (11) after EOF was reached');
		}
		return;
	}
	return unless $size < 1000;
	$max_read = (1100 - $size);
	if($max_read > ($bytes_left - $size)) {
		$max_read = ($bytes_left - $size);
	}
	$ret = sysread STDIN, $buffer, $max_read, $size;
	if($ret == 0) {
		$the_end = 1;
	} elsif($ret == undef) {
		die("read returned: " . $!);
	}
}

# remove x bytes from buffer and return them
# read_line doesn't use this, but keeps bytes_count anyway
sub read_buff {
	my $count = shift;
	my $str;
	$str = substr $buffer, 0, $count;
	$buffer = substr $buffer, $count;
	$bytes_left -= $count;
	return $str;
}

# mark the entire buffer as used
sub buffer_used {
	$bytes_left -= length $buffer;
	$buffer = '';
}

# returns the next line from the input stream (not including the trailing crlf)
sub read_line {
	my $size;
	my $crlf_index;
	my $line;
	refill_buffer();
	$size = length $buffer;
	$crlf_index = index $buffer, "\r\n";
	if($crlf_index < 0) {
		die("expected a line, but didn't find a CRLF for $size characters (bytes_left: $bytes_left)");
	}
	$line = substr $buffer, 0, $crlf_index;
	$buffer = substr $buffer, ($crlf_index + 2);
	$bytes_left -= $crlf_index + 2;
	return $line;
}

sub parse_main_headers {
	my $line;
	my $i;

	$line = read_line;
	$i = index($line, '/');
	die(500) if $i < 0;
	$line = substr($line, $i + 1);
	$i = index($line, ' ');
	die(501) if $i < 0;
	$line = substr($line, 0, $i);

	if($line eq '') {
		# FIXME return 404?
		die('no filename passed');
	}

	$line = lc($line);
	$line =~ s/[^a-z0-9.-]/_/g;
	$line =~ s/^[.-]/_/;

	$g_filename = $line;
	


	while(1) {
		$line = read_line;
		if(substr(lc($line), 0, 16) eq 'content-length: ') {
			$content_length = substr($line, 16);
		} elsif(substr(lc($line), 0, 14) eq 'content-type: ') {
			$i = index(lc($line), 'boundary=');
			if($i < 0) {
				die('no boundary= in content-type header');
			}
			$boundary = substr $line, ($i + 9);
		} elsif($line eq '') {
			if($content_length == -1) {
				die('No Content-Length header');
			}
			if($boundary eq "") {
				die('No boundary found in headers');
			}
			$boundary = '--' . $boundary;
			$bytes_left = $content_length;
			return;
		}
	}
}

# pass int from 0-1000
sub progress_bar_update {
	my $pct = shift;
	my $dots;
	if($pct > $progress_written) {
		syswrite(PROGRESS_FD, '............................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................', $pct - $progress_written);
		$progress_written = $pct;
	}
}

sub progress_bar_start {
	my $progress_filename = shift; # global
	$progress_written = 0;
	$bytes_written = 0;
	open PROGRESS_FD, ">$progress_filename";
}

sub progress_bar_finish {
	progress_bar_update(1000);
	close PROGRESS_FD;
}


# save bytes past and update progress bar
sub output {
	my $out = shift;
	my $prog;
	print FD $out;

	# update progressbar
	$bytes_written += length($out);
	$prog = $bytes_written / $content_length; # FIXME off by size of headers. do we care?
	$prog = int($prog * 999 + .99);
	progress_bar_update($prog);
}

sub save_to_next_boundary {
	my $filename = shift;
	my $i;
	my $crlfboundary = "\r\n$boundary";
	open FD, ">$output_path/partial/$filename";
	progress_bar_start("$output_path/progress/$filename");
	while(1) {
		refill_buffer;
		$i = index $buffer, $crlfboundary;
		if($i < 0) {
			output $buffer;
			buffer_used;
		} else {
			if ($i > 0) {
				output(read_buff($i));
			}
			read_buff(2); # remove crlf between content and boundary #FIXME make sure this exists
			close FD;
			progress_bar_finish();
			return;
		}
	}
}


sub parse_sub {
	my $sub_length = -1;
	my $line;
	my $i;
	my $i2;
	
	while(1) {
		$line = lc(read_line());
		if($line eq "") {
			return save_to_next_boundary($g_filename);
		}
		#if(substr($line, 0, 21) eq 'content-disposition: ') {
		#	$i = index($line, 'filename="');
		#	if($i < 0) {
		#		die('no filename=" in content-disposition sub-header');
		#	}
		#	$i2 = index($line, '"', ($i + 10));
		#	if($i2 < 0) {
		#		die('no filename=" in content-disposition sub-header');
		#	}
		#	$filename = lc(substr($line, ($i + 10), ($i2 - ($i + 10))));
		#	$filename =~ s/[^a-z0-9.-]/_/g;
		#	$filename =~ s/^[.-]/_/;
		#} elsif($line eq '') {
		#	if($filename eq "") {
		#		die('No filename found in headers on part');
		#	}
		#	return save_to_next_boundary($filename);
		#}
	}
}

sub reply_and_quit {
	print "HTTP/1.1 200 OK\r\nConnection: close\r\nContent-Type: text/plain\r\nContent-Length: 8\r\n\r\nReceived";
	exit 0;
}

sub parse_body {
	my $line;

	while(1) {
		$line = read_line;
		if($line eq $boundary) {
			parse_sub;
		} elsif($line eq ($boundary . '--')) {
			reply_and_quit;
		} else {
			die("Expecting boundary \"$boundary\" but got: \"$line\"");
		}
	}
}


#$flags = '';
#fcntl(STDIN, F_GETFL, $flags)
#    or die "Couldn't get flags for STDIN : $!\n";
#$flags |= O_NONBLOCK;
#fcntl(STDIN, F_SETFL, $flags)
#    or die "Couldn't set flags for STDIN: $!\n";
parse_main_headers;
parse_body;
